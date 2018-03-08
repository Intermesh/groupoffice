/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountsGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.email.AccountsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.layout='fit';
	config.border=false;
	config.autoScroll=true;
	config.store = new GO.data.JsonStore({
		url: GO.url("email/account/store"),
		fields:['id','email','host', 'user_name', 'username','smtp_host'],
		remoteSort: true,
		baseParams:{permissionLevel:GO.permissionLevels.write},
		sortInfo:{field: 'email', direction: "ASC"}
	});	
	config.paging=true;
	
	var columnModel = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header:GO.lang.strEmail,
			dataIndex: 'email'
		},{
			header:GO.lang.strUsername,
			dataIndex: 'username'
		},{
			header:GO.lang.strOwner,
			dataIndex: 'user_name',
			sortable: false
		},{
			header:GO.email.lang.host,
			dataIndex: 'host'
		},{
			header:'SMTP',
			dataIndex: 'smtp_host'
		}]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.lang['strNoItems']		
	});

	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
	});
	config.tbar = [{
		iconCls: 'btn-add',
		text: GO.lang.cmdAdd,
		cls: 'x-btn-text-icon',
		handler: function(){
			
			this.showAccountDialog();
		},
		scope: this,
		disabled: !GO.settings.modules.email.write_permission
	},{
		iconCls: 'btn-delete',
		text: GO.lang.cmdDelete,
		cls: 'x-btn-text-icon',
		handler: function(){
			this.deleteSelected({
				callback: function(){
					if(GO.email.aliasesStore.loaded)
					{
						GO.email.aliasesStore.reload();
					}
					this.fireEvent('delete', this);
				},
				scope: this
			});
		},
		scope:this,
		disabled: !GO.settings.modules.email.write_permission
	},'-',GO.lang['strSearch'] + ':', this.searchField];
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;

	this.accountDialog = new GO.email.AccountDialog();
	this.accountDialog.on('save', function(){
	this.store.reload();
		if(GO.email.aliasesStore.loaded)
		{
			GO.email.aliasesStore.reload();
		}
	}, this);
	
	GO.email.AccountsGrid.superclass.constructor.call(this, config);	

	this.addEvents({'delete':true});

	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);
	
		this.showAccountDialog(record.data.id);

	}, this);

};

Ext.extend(GO.email.AccountsGrid, GO.grid.GridPanel,{
	showAccountDialog : function(account_id){

		
		this.accountDialog.show(account_id);
	}
});