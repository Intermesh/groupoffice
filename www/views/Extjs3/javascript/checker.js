
GO.CheckerWindow = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title=t("Reminders");
	config.maximizable=true;
	config.layout='fit';
	config.modal=false;
	config.resizable=true;
	config.closeAction='hide';
	
	if(!config.width)
		config.width=600;
	if(!config.height)
		config.height=500;


	config.buttons=[{
		text: t("Close"),
		handler: function(){
			this.hide();
		},
		scope:this
	}
	];

	GO.checkerSnoozeTimes = [
	[300,'5 '+t("Minutes")],
	[600, '10 '+t("Minutes")],
	[1200, '20 '+t("Minutes")],
	[1800, '30 '+t("Minutes")],
	[3600, '1 '+t("Hour")],
	[7200, '2 '+t("Hours")],
	[10800, '3 '+t("Hours")],
	[14400, '4 '+t("Hours")],
	[86400, '1 '+t("Day")],
	[2*86400, '2 '+t("Days")],
	[3*86400, '3 '+t("Days")],
	[4*86400, '4 '+t("Days")],
	[5*86400, '5 '+t("Days")],
	[6*86400, '6 '+t("Days")],
	[7*86400, '7 '+t("Days")]
	];

	var snoozeMenuItems = [];

	for(var i=0,max=GO.checkerSnoozeTimes.length;i<max;i++){
		snoozeMenuItems.push(	{
			text: GO.checkerSnoozeTimes[i][1],
			value: GO.checkerSnoozeTimes[i][0],
			handler:function(i){
				this.checkerGrid.doTask('snooze_reminders', i.value);
			},
			scope: this
		});
	}
	
	var snoozeMenu = new Ext.menu.Menu({
		items:snoozeMenuItems
	});

	config.tbar=[{
		iconCls:'btn-delete',
		text:t("Dismiss"),
		handler: function(){			
			this.checkerGrid.doTask('dismiss_reminders');
		},
		scope: this
	},
	{
		iconCls:'ic-timer',
		text:t("Snooze"),
		menu:snoozeMenu
	},'-',
	{
		iconCls:'btn-select-all',
		text:t("Select all"),
		handler: function(){			
			this.checkerGrid.getSelectionModel().selectAll();
		},
		scope: this
	}
	];
	
	this.checkerGrid = new GO.CheckerPanel();
	config.items=this.checkerGrid;

	config.listeners={
		scope:this,
		show:function(){
			GO.blinkTitle.blink(this.checkerGrid.store.getCount()+' '+t("Reminders"));
		},
		hide: function(){
			GO.blinkTitle.blink(false);
		}
	};
	
	GO.CheckerWindow.superclass.constructor.call(this, config);
	
	this.addEvents({
		changed : true
	});

};

Ext.extend(GO.CheckerWindow, GO.Window,{
	
	
	
	});


GO.CheckerPanel = Ext.extend(function(config){
	
	if(!config)
	{
		config = {};
	}

	config.id='go-checker-panel';
		
		
	config.store = new Ext.data.GroupingStore({
		reader: new Ext.data.JsonReader({
			totalProperty: "count",
			root: "results",
			id: "id",
			fields:[
			'id',
			'name',
			'description',
			'model_id',
			'model_name',
			'model_type_id',
			'type',
			'local_time',
			'iconCls',
			'time',
			'snooze_time',
			'text'
			]
		}),
		groupField:'type',
		remoteSort: true,
		remoteGroup: true
//		sortInfo: {
//			field: 'time',
//			direction: 'ASC'
//		}
	});

	var action = new Ext.ux.grid.RowActions({
		header : '-',
		autoWidth:true,
		align : 'center',
		actions : [{
			iconCls : 'ic-timer',
			qtip: t("Snooze")
		},{
			iconCls : 'btn-delete',
			qtip:t("Dismiss")
		}]
	});

	action.on({
		scope:this,
		action:function(grid, record, action, row, col) {

			grid.getSelectionModel().selectRow(row);

			switch(action){
				case 'ic-timer':
					this.doTask('snooze_reminders', record.get('snooze_time'));
					break;
				case 'btn-delete':
					this.doTask('dismiss_reminders');
					break;
			}
		}
	}, this);

 
	config.cm = new Ext.grid.ColumnModel([
	{
		dataIndex: 'type',
		hideable: false
	},{
		header: "",
		width:28,
		dataIndex: 'icon',
		renderer: this.iconRenderer,
		hideable: false,
		groupable: false
	},
	{
		header:t("Time"),
		dataIndex: 'local_time',
		width: dp(120),
		groupable: false
	},
	{
		header:t("Name"),
		dataIndex: 'name',
		id:'name',
		groupable: false
	},
	{
		width:80,
		header:t("Snooze"),
		dataIndex: 'snooze_time',
		renderer : this.renderSelect.createDelegate(this),
		editor:new GO.form.ComboBox({
			store : new Ext.data.ArrayStore({
				idIndex:0,
				fields : ['value', 'text'],
				data : GO.checkerSnoozeTimes
			}),
			valueField : 'value',
			displayField : 'text',
			mode : 'local',
			triggerAction : 'all',
			editable : false,
			selectOnFocus : true,
			forceSelection : true
		}),
		groupable: false
	},
	action]);

	config.plugins=[action];

	config.clicksToEdit=1;

	config.autoExpandColumn='name';

	config.view=new Ext.grid.GridView({
		enableRowBody:true,
		showPreview:true,
		forceFit:true,
		autoFill: true,
		getRowClass : function(record, rowIndex, p, ds) {

			var cls = rowIndex%2 == 0 ? 'odd' : 'even';

			if (this.showPreview) {
				p.body = '<div class="description">' +record.data.content + '</div>';
				return 'x-grid3-row-expanded '+cls;
			}
			return 'x-grid3-row-collapsed';
		},
		emptyText: t("No items to display")
	});
			
	config.view=  new Ext.grid.GroupingView({
		hideGroupedColumn:true,
		groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'+t("items")+'" : "'+t("item")+'"]})',
		emptyText: t("No items to display"),
		showGroupName:false,
		enableRowBody:true,
		getRowClass : function(record, rowIndex, p, ds) {

			if(!GO.util.empty(record.data.text)){
				p.body = '<div class="description go-html-formatted">' +record.data.text + '</div>';
				return 'x-grid3-row-expanded';
			}else
			{
				return 'x-grid3-row-collapsed';
			}
		}
	});
	config.selModel = new Ext.grid.RowSelectionModel();
	config.loadMask=true;	
	
	GO.grid.GridPanel.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function (grid, index){
		
		var selectionModel = grid.getSelectionModel();
		var record = selectionModel.getSelected();
		
		if(!record.data.model_name || !record.data.model_id) {
			return;
		}
		
		var parts = record.data.model_name.split("\\");		
		go.Router.goto(parts[3].toLowerCase()+"/"+record.data.model_id);
		
	}, this);
	
	
},Ext.grid.EditorGridPanel, {
	doTask : function(task, seconds)
	{
		this.stopEditing();
		
		var selected = this.selModel.getSelections();

		if(!selected.length)
		{
			Ext.MessageBox.alert(t("Error"), t("You didn't select an item."));
		}else
		{
			var reminders = [];

			for (var i = 0; i < selected.length;  i++)
			{
				reminders.push(selected[i].get('id'));
			}
			
			var url = task=='snooze_reminders' ? GO.url('reminder/snooze') : GO.url('reminder/dismiss');

			Ext.Ajax.request({
				url: url,
				params: {
					task:task,
					snooze_time: seconds,
					reminders: Ext.encode(reminders)
				},
				callback: function(){
					for (var i = 0; i < selected.length;  i++)
					{
						this.store.remove(selected[i]);
					}

					GO.checker.lastCount=this.store.getCount();

					if(!GO.checker.lastCount){
						this.ownerCt.hide();
						GO.checker.reminderIcon.setDisplayed(false);
					}
				},
				scope: this
			});
		}
	},
	iconRenderer : function(src,cell,record){
		return '<div class=\"go-icon ' + record.data.iconCls +' \"></div>';
	},
	renderSelect : function(value, p, record, rowIndex, colIndex, ds) {
		var cm = this.getColumnModel();
		var ce = cm.getCellEditor(colIndex, rowIndex);

		var val = value;
		if (ce.field.store.getById(value) !== undefined) {

			var r = ce.field.store.getById(value);
			val = ce.field.store.getById(value).get("text");
		}
		return val;
	}


});

GO.Checker = function(){
	this.addEvents({
		'alert' : true,
		'startcheck' : true,
		'endcheck' : true
	});
			
	this.checkerWindow = new GO.CheckerWindow();
			
	
	
	
};

Ext.extend(GO.Checker, Ext.util.Observable, {

	lastCount : 0,
	params:{
		requests: {
			reminders: {
				r:"reminder/store"
			},
			loginstatus:{
				r:"core/auth/checkclient"
			}
		}
	},
  
	callbacks : {},
	
	init : function(){
		
		this.reminderIcon = Ext.get("reminder-icon");
	this.reminderIcon.setDisplayed(false);
	this.reminderIcon.on('click', function(){
		this.checkerWindow.show();
	}, this);   
		//this.fireEvent('startcheck', this);

		Ext.TaskMgr.start({
			run: this.checkForNotifications,
			scope:this,
			interval: GO.settings.config.checker_interval*1000,
						// interval: 10000 // debug / test config
		});
	},
  
	// See modules/email/EmailClient.js and search for "GO.checker.registerRequest" for an usage example
	registerRequest : function(url, params, callback, scope){
		params.r=url;	
		if(url == 'email/account/checkUnseen')
			var requestId = 'emails';
		else
			var requestId=Ext.id();
		this.params.requests[requestId] = params;	
		this.callbacks[requestId] = {
			callback:callback,
			scope:scope
		};
	},
  
	// Function to check for reminders in the database
	checkForNotifications : function(){
	
		var params = {
			requests: Ext.encode(this.params.requests)
			};
	
		Ext.Ajax.request({
			url: GO.url('core/multiRequest'),	  
			params: params,
			callback: function(options, success, response)
			{
				if(!success)
				{
				//Ext.MessageBox.alert(t("Error"), "Connection to the internet was lost. Couldn't check for reminders.");
				//silently ignore
				}else
				{
					var result = Ext.decode(response.responseText);
					
					var data = {
						alarm:false,
						popup:false,
						getParams:{}
					};	
					
					for(var id in result){
						if(id=="reminders") {
							this.handleReminderResponse(result[id], {
								alarm:false,
								popup:false,
								getParams:{}
							});
						} else if(id=="loginstatus") {
							this.handleLoginstatusResponse(result[id]);
						}	else if (id!='success' && id!='feedback') {
							if(this.callbacks[id]) {
								this.callbacks[id].callback.call(this.callbacks[id].scope, this, result[id],data);
							}
						}
						if (id=="emails" && result[id].email_status) {

							if((!result[id].email_status.has_new && this.countEmailShown) 
											|| result[id].email_status.total_unseen <= 0  
											|| (this.countEmailShown && this.countEmailShown >= result[id].email_status.total_unseen)){
								
								this.countEmailShown = result[id].email_status.total_unseen;
								continue;
							} else {
								this.countEmailShown = result[id].email_status.total_unseen;
							}
							
//							if (this.countEmailShown && (!result[id].email_status.has_new || result[id].email_status.total_unseen <= 0)) {
//								return;
//							}
							
							if(GO.util.empty(GO.settings.mute_new_mail_sound)){
								GO.playAlarm('message-new-email');
							}
							
							if (GO.settings.popup_emails) {
								this.triggerEmailNotification(result[id].email_status);
							}
						}
					}
					
					
				}
			//this.fireEvent('endcheck', this, data);
			},
			scope:this
		});
	},
	
	notifyDesktop : function(storeData){
		var convertResultsToText = function(storeData) {

			var notificationText = '';

			for (var i = 0, l = storeData.results.length; i < l; i++) {
				notificationText += storeData.results[i].type+': ';
				notificationText += storeData.results[i].name+' [';
				notificationText += storeData.results[i].time+']';
			}		
			return notificationText;

		};

		var notificationText = convertResultsToText(storeData);
		var title = t("Reminders");
		var options = {
			body: notificationText,
			icon: 'views/Extjs3/themes/Group-Office/images/32x32/reminder.png'
		};

		if (!("Notification" in window)) {
			return;
		}

		if (Notification.permission === "granted") {
			var notification = new Notification(title,options);
		} else if (Notification.permission !== 'denied' || Notification.permission === "default") {
		  Notification.requestPermission(function (permission) { // ask first
			if (permission === "granted") {
			  var notification = new Notification(title,options);
			}
		  });
		}
	},
	
	showPopup : function(data) {
		if(GO.util.isMobileOrTablet()) {
			return;
		}
		GO.reminderPopup = GO.util.popup({
			width:400,
			height:400,
			url:GO.url("reminder/display", data.getParams),
			target:'groupofficeReminderPopup',
			position:'br',
			closeOnFocus:false
		});
	},
	
	triggerEmailNotification : function(email_status) {

		this.countEmailShown = true;
		var title = t("New email");
		var options = {
			body: t("You have %d unread email(s)").replace('%d',email_status.total_unseen),
			icon: 'modules/email/themes/Group-Office/images/22x22/email.png'
		};

		if (!("Notification" in window)) {
			return;
		}

		if (Notification.permission === "granted") {
			var notification = new Notification(title,options);
		} else if (Notification.permission !== 'denied' || Notification.permission === "default") {
		  Notification.requestPermission(function (permission) { // ask first
			if (permission === "granted") {
			  var notification = new Notification(title,options);
			}
		  });
		}
		
	},
	
  
	handleReminderResponse : function(storeData, data){
//		this.fireEvent('check', this, data);
		if(storeData.total && storeData.total > 0) {
			this.checkerWindow.checkerGrid.store.loadData(storeData);
			if(this.lastCount != this.checkerWindow.checkerGrid.store.getCount())
			{
				this.lastCount = this.checkerWindow.checkerGrid.store.getCount();
				if(this.lastCount>0)
					this.checkerWindow.show();
				else
					this.checkerWindow.hide();

				this.reminderIcon.setDisplayed(true);

				data.alarm=true;
				data.popup=true;			
			}
			if(data.popup && !GO.util.empty(GO.settings.popup_reminders)){
				if (!("Notification" in window)) {
					this.showPopup(data);
				} else {
					this.notifyDesktop(storeData);
				}
			}
			
		} else {
			this.reminderIcon.setDisplayed(false);
		}
		
		if(data.alarm && GO.util.empty(GO.settings.mute_reminder_sound)){
			GO.playAlarm('message-new-email');				
		}

		
	},
	
	handleLoginstatusResponse : function(data){
		
		// If the login is not valid anymore, then the user is logged out and the browser will be redirected to the login screen
		if(!data.loginValid){     
			document.location.href=BaseHref;
		}

	}

});
