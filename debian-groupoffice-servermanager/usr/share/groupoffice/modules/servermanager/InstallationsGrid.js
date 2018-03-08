/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: InstallationsGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */
 
GO.servermanager.InstallationsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	

	
	config.title = t("installations", "servermanager");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		url: GO.url("servermanager/installation/store"),
		baseParams: {
			task: 'installations'
		},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','status','name','webmaster_email','title','default_country','language','default_timezone','default_currency','default_date_format','default_date_separator','default_thousands_separator','theme','allow_themes','allow_password_change','allow_registration','allow_duplicate_email','auto_activate_accounts','notify_admin_of_registration','registration_fields','required_registration_fields','register_modules_read','register_modules_write','register_user_groups','register_visible_user_groups','max_users','ctime','mtime','count_users', 'install_time','lastlogin','total_logins','database_usage','file_storage_usage','mailbox_usage','total_usage', 'comment', 'ctime', 'serverclient_domains', 'features', 'enabled','quota'],
		remoteSort: true
	});
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: t("Name"), 
			dataIndex: 'name'
		},{
			header: t("status", "servermanager"),
			dataIndex: 'status'
		},		{
			header: t("webmasterEmail", "servermanager"), 
			dataIndex: 'webmaster_email',
			sortable:false
		},		{
			header: t("title", "servermanager"), 
			dataIndex: 'title',
			sortable:false
		},	{
			header: t("maxUsers", "servermanager"), 
			dataIndex: 'max_users'
		},		{
			header: t("Created at"), 
			dataIndex: 'ctime',
			width:110
		},		{
			header: t("Modified at"), 
			dataIndex: 'mtime',
			width:110
		},	{
			header: t("countUsers", "servermanager"),
			dataIndex: 'count_users',
			align:'right'
		},		{
			header: t("lastlogin", "servermanager"),
			dataIndex: 'lastlogin'
		},		{
			header: t("totalLogins", "servermanager"),
			dataIndex: 'total_logins',
			align:'right'
		},		{
			header: t("databaseUsage", "servermanager"),
			dataIndex: 'database_usage',
			align:'right'
		},
		{
			header: t("fileStorageUsage", "servermanager"),
			dataIndex: 'file_storage_usage',
			align:'right'
		},{
			header: t("quota", "servermanager"),
			dataIndex: 'quota',
			align:'right'
		},
		{
			header: t("mailboxUsage", "servermanager"),
			dataIndex: 'mailbox_usage',
			align:'right'
		},
		{
			header: t("totalUsage", "servermanager"),
			dataIndex: 'total_usage',
			align:'right'
		}
//		,{
//			header: t("strComment", "servermanager"),
//			dataIndex: 'comment'
//		}
		,{
			header: t("mailDomains", "servermanager"),
			dataIndex: 'serverclient_domains',
			sortable:false
		}
		]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display"),
		getRowClass : function(record, rowIndex, p, store){
			if(GO.util.empty(record.data.enabled)){
				return 'installation-disabled';
			}
		}
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
	});
	
	
	config.tbar=new Ext.Toolbar({
		cls:'go-head-tb',
		items:[{
				xtype:'htmlcomponent',
				html:t("installations", "servermanager"),
				cls:'go-module-title-tbar'
			},{
			iconCls: 'btn-add',
			text: t("Add"),
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.servermanager.installationDialog.show();
			},
			scope: this
		},{
			iconCls: 'btn-delete',
			text: t("Delete"),
			cls: 'x-btn-text-icon',
			handler: function(){
				this.deleteSelected();
			},
			scope: this
		},
		'-',
		{
			iconCls: 'btn-addressbook-manage',
			text: t("Administration"),
			cls: 'x-btn-text-icon',
			handler:function(){
				if(!this.manageDialog)
				{
					this.manageDialog = new GO.servermanager.ManageDialog();
				}
				this.manageDialog.show();
			},
			scope: this
		},
//		'-',
//		{
//			iconCls: 'btn-add',
//			text: 'Test billing connection',
//			cls: 'x-btn-text-icon',
//			handler: function(){
//				var reqconf = { 
//					url: 'servermanager/installation/testBilling',
//					success: function() { alert(t("testBillingConnection", "servermanager")); }
//				};
//
//				GO.request(reqconf);
//			},
//			scope: this
//		},
		'-',
		t("Search")+': ', ' ',this.searchField]		
	});
		    			    		
	GO.servermanager.installationDialog.on('save', function(){   
		this.store.reload();	    			    			
	}, this);
	
	
	
	GO.servermanager.InstallationsGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		
		GO.servermanager.installationDialog.show(record.data.id);
	}, this);
	
};

Ext.extend(GO.servermanager.InstallationsGrid, GO.grid.GridPanel,{
	afterRender : function(){
		GO.servermanager.InstallationsGrid.superclass.afterRender.call(this);
		
		this.store.load();
	}
});