/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SelectGroups.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
/**
 * @class GO.dialog.SelectGroups
 * @extends Ext.Window
 * A window to select a number of Group-Office user groups.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object} config The config object
 */
 
GO.dialog.SelectGroups = function(config){
	
	Ext.apply(this, config);
	

	this.store = new GO.data.JsonStore({
		url: GO.url('core/groups'),
		baseParams: {
			hideUserGroups: true
		},
		fields: ['id','name','user_id','user_name'],
		remoteSort: true		
	});
    
	var action = new Ext.ux.grid.RowActions({
		header : '-',
		autoWidth:true,
		align : 'center',
		actions : [{
			iconCls : 'btn-users',
			qtip: t("Users")
		}]
	});

	this.grid = new GO.grid.GridPanel({
		plugins : action,
		clicksToEdit : 1,
		paging:true,
		border:false,
		store: this.store,
		view: new Ext.grid.GridView({
			autoFill: true,
			forceFit: true
		}),
		columns: [{
			header: t("Name"),
			dataIndex: 'name',
			css: 'white-space:normal;',
			sortable: true
		},action],
		sm: new Ext.grid.RowSelectionModel()			
	});

	action.on({
		scope:this,
		action:function(grid, record, action, row, col) {

			this.grid.getSelectionModel().selectRow(row);

			switch(action){
				case 'btn-users':
					this.showUsersInGroupDialog(record.data.id);
					break;
			}
		}
	}, this);


	this.grid.on('rowdblclick', function(){
		this.callHandler(true);
	}, this);
		
	this.store.load();

	
	Ext.Window.superclass.constructor.call(this, {
		layout: 'fit',
		modal:false,
		height:400,
		width:600,
		closeAction:'hide',
		title:t("Select groups"),
		items: this.grid,
		buttons: [
		{
			text: t("Ok"),
			handler: function (){
				this.callHandler(true);
			},
			scope:this
		},
		{
			text: t("Add"),
			handler: function (){
				this.callHandler(false);
			},
			scope:this
		},
		{
			text: t("Close"),
			handler: function(){
				this.hide();
			},
			scope: this
		}
		],
		keys: [{
			key: Ext.EventObject.ENTER,
			fn: function (){
				this.callHandler(true);
			},
			scope:this
		}]
	});
};

Ext.extend(GO.dialog.SelectGroups, Ext.Window, {


	// private
	showUsersInGroupDialog : function(groupId) {
		if (!this.usersInGroupDialog) {
			this.usersInGroupDialog = new GO.dialog.UsersInGroup();
		}
		this.usersInGroupDialog.setGroupId(groupId);
		this.usersInGroupDialog.show();
	},
	
	//private
	callHandler : function(hide){
		if(this.handler)
		{
			if(!this.scope)
			{
				this.scope=this;
			}
			
			var handler = this.handler.createDelegate(this.scope, [this.grid]);
			handler.call();
		}
		if(hide)
		{
			this.hide();
		}
	}	
	
});


