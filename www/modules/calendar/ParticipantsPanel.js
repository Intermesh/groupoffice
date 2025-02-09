/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ParticipantsPanel.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.calendar.Participant = Ext.data.Record.create([
// the "name" below matches the tag name to read, except "availDate"
// which is mapped to the tag "availability"
{
	name : 'id',
	type : 'string'
}, {
	name : 'name',
	type : 'string'
}, {
	name : 'email',
	type : 'string'
}, {
	name : 'create_permission',
	type : 'string'
},{
	name : 'available',
	type : 'string'
}, {
	name : 'status',
	type : 'string'
}, {
	name : 'is_organizer',
	type : 'int'
}

]);

GO.calendar.ParticipantsPanel = function(eventDialog, config) {

	this.eventDialog = eventDialog;

	if (!config) {
		config = {};
	}

	config.hideMode = 'offsets';

	config.store = new GO.data.JsonStore({
		url : GO.url('calendar/participant/store'),
		baseParams : {
			task : "participants"
		},
		fields : ['id', 'name', 'email', 'available','status', 'user_id', 'contact_id','is_organizer','create_permission']
	});
		
	var tbar = [
		{
			width: dp(300),
			xtype: "searchemailcombo",
			listeners: {
				select: function(c, record) {
					if(record.data.entity == "User") {
						GO.request({
							url: "calendar/participant/getUsers",
							params: {
								users: Ext.encode([record.data.entityId]),
								start_time: this.eventDialog.getStartDate().format('U'),
								end_time: this.eventDialog.getEndDate().format('U')
							},
							success: function (response, options, result) {
								this.addParticipants(result);
							},
							scope: this
						});
					} else
					{
						GO.request({
							url: "calendar/participant/getContacts",
							params: {
								contacts: Ext.encode([record.data]),
								start_time: this.eventDialog.getStartDate().format('U'),
								end_time: this.eventDialog.getEndDate().format('U')
							},
							success: function (response, options, result) {
								this.addParticipants(result);
							},
							scope: this
						});
					}
					c.reset();
				},
				scope: this
			}
		},
		"->",
		this.checkAvailabilityButton = new Ext.Button({
			iconCls : 'ic-event-available',
			text : t("Check availability", "calendar"),
			handler : function() {
				this.eventDialog.checkAvailability();
			},
			scope : this
		}),
	{
		iconCls : 'btn-add',
		tooltip : t("Add"),
		cls : 'x-btn-text-icon',
		handler : function() {
			this.showAddParticipantsDialog();
		},
		scope : this
	}, {
		iconCls : 'btn-delete',
		tooltip : t("Delete"),
		cls : 'x-btn-text-icon',
		handler : function() {
			this.gridPanel.deleteSelected();
		},
		scope : this
	}];

	this.gridPanel = new GO.grid.GridPanel(
	{
		layout:'fit',
		split:true,
		store: config.store,		
		region:'center',
		columns : [{
			header : t("Name"),
			dataIndex : 'name'
		}, {
			header : t("E-mail"),
			dataIndex : 'email'
		}, {
			header : t("Status"),
			dataIndex : 'status',
			renderer : function(v) {
				switch (v) {
					case 'TENTATIVE' :
						return t("Tentative", "calendar");
						break;
						
					case 'DECLINED' :
						return t("Declined", "calendar");
						break;

					case 'ACCEPTED' :
						return t("Accepted", "calendar");
						break;

					case 'NEEDS-ACTION' :
						return t("Not responded yet", "calendar");
						break;
				}
			}
		}, {
			header : t("Available"),
			dataIndex : 'available',
			renderer : function(v) {

				var className = 'ic-help warning';
				if(v!='?')
					className = v ? 'ic-check-circle success' : 'ic-cancel danger';
				
				return '<div class="icon ' + className + '"></div>';
			}
		}, {
			header : t("Create permission", "calendar"),
			dataIndex : 'create_permission',
			width:140,
			renderer : function(v) {

				var className = v ? 'ic-check-circle success' : 'ic-cancel warning';
				
				return '<div class="icon ' + className + '"></div>';
			}
		}, {
			header : t("Organizer", "calendar"),
			dataIndex : 'is_organizer',
			renderer : function(v) {
				var className = v ? 'ic-done success' : '';		

				return '<div class="icon ' + className + '"></div>';
			}
		}],
		view : new Ext.grid.GridView({
			autoFill : true,
			forceFit : true
		}),
		loadMask : {
			msg : t("Loading...")
		},
		sm : new Ext.grid.RowSelectionModel(),
		deleteSelected : function(){
			var selectedRows = this.selModel.getSelections();
			for (var i = 0; i < selectedRows.length; i++) {
				selectedRows[i].commit();
				
				if(selectedRows[i].data.is_organizer){
					alert(t("You can't remove the organizer", "calendar"));
					return;
				}
				
				this.store.remove(selectedRows[i]);
			}
		}
	});
		
	
	Ext.apply(config, {
		title : t("Participants", "calendar"),
		border : false,
		tbar:tbar,
		layout : 'fit',
		items: [this.gridPanel]
	});

	config.store.setDefaultSort('name', 'ASC');

	GO.calendar.ParticipantsPanel.superclass.constructor.call(this, config);

};

Ext.extend(GO.calendar.ParticipantsPanel, Ext.Panel, {

	event_id : 0,
	
	newId: 0,
	
	loaded : false,

	getGridData : function(){
		return this.gridPanel.getGridData();
	},
	
	setEventId : function(event_id) {
		this.event_id = this.store.baseParams.event_id = event_id;
		this.store.loaded = false;
		if(this.event_id==0)
		{
			this.store.removeAll();
		}
		this.newId=0;		
	},

	invitationRequired : function(){
		//invitation is required if there's a participant that is not the current user.
		if(this.store.getCount()>1) {
			return true;
		}
		
		var records = this.store.getRange();
		for(var i=0;i<records.length;i++) {
			if(!records[i].data.is_organizer) {
				return true;
			}
		}
	
		return false;
		
	},

	showAddParticipantsDialog : function() {
		var select = new go.util.SelectDialog ({
			scope: this,
			entities: ["Contact", "User"],
			selectSingleEmail: function(name, email, id, entityName) {

				switch(entityName) {
					case "User":
							GO.request({
								url:"calendar/participant/getUsers",
								params:{
									users: Ext.encode([id]),
									start_time : this.eventDialog.getStartDate().format('U'),
									end_time : this.eventDialog.getEndDate().format('U')
								},
								success:function(response, options, result){
									this.addParticipants(result);
								},
								scope:this
							});					
					break;

					case "Contact":
							GO.request({
								url:"calendar/participant/getContacts",
								params:{
									contacts: Ext.encode([{entityId: id, name: name, email: email}]),
									start_time : this.eventDialog.getStartDate().format('U'),
									end_time : this.eventDialog.getEndDate().format('U')
								},
								success:function(response, options, result){
									this.addParticipants(result);
								},
								scope:this
							});	
					break;
				}
				
			},
			selectMultiple: function(ids, entityName) {
				switch(entityName) {
					case "User":
							GO.request({
								url:"calendar/participant/getUsers",
								params:{
									users: Ext.encode(ids),
									start_time : this.eventDialog.getStartDate().format('U'),
									end_time : this.eventDialog.getEndDate().format('U')
								},
								success:function(response, options, result){
									this.addParticipants(result);
								},
								scope:this
							});			
					break;

					case "Contact":
						GO.request({
							url:"calendar/participant/getContacts",
							params:{
								contacts: Ext.encode(ids.map(function(id){return {entityId: id};})),
								start_time : this.eventDialog.getStartDate().format('U'),
								end_time : this.eventDialog.getEndDate().format('U')
							},
							success:function(response, options, result){
								this.addParticipants(result);
							},
							scope:this
						});		
						break;
					}
			}
		});
		select.show();
	},
	
	
	addParticipants : function(result){		
		var filtered=[];
		for(var i=0;i<result.results.length;i++){
			var email = result.results[i].email;
			var record = this.store.find("email", email, 0, false);
			if(record==-1){
				filtered.push(result.results[i]);
			}
		}
		result.results=filtered;
		this.store.loadData(result, true);
	},
	
	reloadOrganizer : function(){
		
		var calendar_id = this.eventDialog.selectCalendar.getValue();
		if(!calendar_id)
			return;
		
		GO.request({
			maskEl:this.eventDialog.win.getEl(),
			url :'calendar/participant/loadOrganizer',
			params : {
				calendar_id : calendar_id,
				start_time : this.eventDialog.getStartDate().format('U'),
				end_time : this.eventDialog.getEndDate().format('U')
			},
			success : function(options, response, result) {
			
				var index = this.store.find("is_organizer", true);
				
				if(this.store.getCount()>1){
					//with more then one participant don't remove any participant.
					//with only one organizer the user didn't make any changes so we can replace it.
					var record = this.store.getAt(index);
					record.set('is_organizer',false);				
				} else {
					this.store.removeAt(index);
				}
				
				var index = this.store.find("email", result.organizer.email);
				if(index>-1) {
					this.store.removeAt(index);
				}
			
				this.store.loadData({results:[result.organizer]}, true);
				
			},
			scope : this
		});
	},
	
	reloadAvailability : function(){

		GO.request({
			url : "calendar/participant/reload",
			params : {
				event_id:this.event_id,
				participants : Ext.encode(this.getGridData()),
				start_time : this.eventDialog.getStartDate().format('U'),
				end_time : this.eventDialog.getEndDate().format('U')
			},
			success : function(options, response, result) {
				for (var i = 0; i < result.results.length; i++) {
					this.store.getAt(i).set('available', result.results[i]['available']);
				}
				this.store.commitChanges();
			},
			scope : this
		});
		
	},
	
	// Return array with name, email and user_id
	getParticipantData : function(){
		var records = this.store.getRange();
		var data = [];
		for (var i = 0; i < records.length; i++) {
			var participant = {};
			participant.name = records[i].get('name')
			participant.email = records[i].get('email');
			participant.user_id = records[i].get('user_id');
			data.push(participant);
		}
		return data;
	},
	
	getParticipantEmails : function() {
		var records = this.store.getRange();
		var emails = [];
		for (var i = 0; i < records.length; i++) {
			emails.push(records[i].get('email'));
		}
		return emails;
	},
					
	getParticipantNames : function() {
		var records = this.store.getRange();
		var names = [];
		for (var i = 0; i < records.length; i++) {
			names.push(records[i].get('name'));
		}
		return names;
	}

});
