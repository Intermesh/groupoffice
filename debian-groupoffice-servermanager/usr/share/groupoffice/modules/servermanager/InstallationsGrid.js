/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: InstallationsGrid.js 16975 2014-03-07 11:24:48Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * @author WilmarVB <wilmar@intermesh.nl>
 */
 
GO.servermanager.InstallationsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	

	
	config.title = GO.servermanager.lang.installations;
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
			header: GO.lang.strName, 
			dataIndex: 'name'
		},{
			header: GO.servermanager.lang.status,
			dataIndex: 'status'
		},		{
			header: GO.servermanager.lang.webmasterEmail, 
			dataIndex: 'webmaster_email',
			sortable:false
		},		{
			header: GO.servermanager.lang.title, 
			dataIndex: 'title',
			sortable:false
		},	{
			header: GO.servermanager.lang.maxUsers, 
			dataIndex: 'max_users'
		},		{
			header: GO.lang.strCtime, 
			dataIndex: 'ctime',
			width:110
		},		{
			header: GO.lang.strMtime, 
			dataIndex: 'mtime',
			width:110
		},	{
			header: GO.servermanager.lang.countUsers,
			dataIndex: 'count_users',
			align:'right'
		},		{
			header: GO.servermanager.lang.lastlogin,
			dataIndex: 'lastlogin'
		},		{
			header: GO.servermanager.lang.totalLogins,
			dataIndex: 'total_logins',
			align:'right'
		},		{
			header: GO.servermanager.lang.databaseUsage,
			dataIndex: 'database_usage',
			align:'right'
		},
		{
			header: GO.servermanager.lang.fileStorageUsage,
			dataIndex: 'file_storage_usage',
			align:'right'
		},{
			header: GO.servermanager.lang.quota,
			dataIndex: 'quota',
			align:'right'
		},
		{
			header: GO.servermanager.lang.mailboxUsage,
			dataIndex: 'mailbox_usage',
			align:'right'
		},
		{
			header: GO.servermanager.lang.totalUsage,
			dataIndex: 'total_usage',
			align:'right'
		}
//		,{
//			header: GO.servermanager.lang.strComment,
//			dataIndex: 'comment'
//		}
		,{
			header: GO.servermanager.lang.mailDomains,
			dataIndex: 'serverclient_domains',
			sortable:false
		}
		]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.lang['strNoItems'],
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
				html:GO.servermanager.lang.installations,
				cls:'go-module-title-tbar'
			},{
			iconCls: 'btn-add',
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				GO.servermanager.installationDialog.show();
			},
			scope: this
		},{
			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			handler: function(){
				this.deleteSelected();
			},
			scope: this
		},
		'-',
		{
			iconCls: 'btn-addressbook-manage',
			text: GO.lang.administration,
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
//					success: function() { alert(GO.servermanager.lang["testBillingConnection"]); }
//				};
//
//				GO.request(reqconf);
//			},
//			scope: this
//		},
		'-',
		GO.lang['strSearch']+': ', ' ',this.searchField]		
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