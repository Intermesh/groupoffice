/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AccountsGrid.js 22112 2018-01-12 07:59:41Z mschering $
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

	var actions = this.initRowActions();
	config.plugins = [actions];
	
	var columnModel = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header:t("E-mail"),
			dataIndex: 'email'
		},{
			header:t("Username"),
			dataIndex: 'username'
		},{
			header:t("Owner"),
			dataIndex: 'user_name',
			sortable: false
		},{
			header:t("Host", "email"),
			dataIndex: 'host'
		},{
			header:'SMTP',
			dataIndex: 'smtp_host'
		},actions]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});

	config.tbar = ['->', {
		xtype:'tbsearch',
		store:config.store
	},{
		iconCls: 'ic-add',
		tooltip: t('Add'),
		disabled: !GO.settings.modules.email.write_permission,
		handler: function (e, toolEl) {
			this.showAccountDialog();
		},
		scope: this
	}];
	
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
	},

	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
				iconCls: 'ic-more-vert'
			}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.showMoreMenu(record, e);
			},
			scope: this
		});

		return actions;

	},

	showMoreMenu: function (record, e) {
		if (!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "view",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							this.showAccountDialog(this.moreMenu.record.id);
						},
						scope: this
					},
					"-",
					{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						disabled: !GO.settings.modules.email.write_permission,
						handler: function () {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
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
						scope: this
					},
				]
			})
		}

		this.moreMenu.record = record;

		this.moreMenu.showAt(e.getXY());
	}
});
