/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: DomainsGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.postfixadmin.DomainsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.url('postfixadmin/domain/store'),
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','user_name','domain','description','alias_count','mailbox_count','maxquota','quota','usage','transport','backupmx','ctime','mtime','active','acl_id','used_quota'],
	    remoteSort: true
	});
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	   			{
			header: t("Domain", "postfixadmin"), 
			dataIndex: 'domain'
		},	{
			header: t("Owner"), 
			dataIndex: 'user_name',
		  sortable: false
		},		{
			header: t("Description"), 
			dataIndex: 'description',
			hidden: true
		},		{
			header: t("Aliases", "postfixadmin"), 
			dataIndex: 'alias_count',
			sortable:false
		},		{
			header: t("Mailboxes", "postfixadmin"), 
			dataIndex: 'mailbox_count',
			sortable:false
		},			{
			header: t("Quota (MB)", "postfixadmin"), 
			dataIndex: 'quota',
			sortable:false
		},	{
			header: t("Used quota", "postfixadmin"), 
			dataIndex: 'used_quota',
			sortable:false
		},			{
			header: t("Usage", "postfixadmin"),
			dataIndex: 'usage',
			sortable:false
		},	{
			header: t("Active", "postfixadmin"), 
			dataIndex: 'active'
		},		{
			header: t("Backup MX", "postfixadmin"), 
			dataIndex: 'backupmx'
		},	{
			header: t("Created at"), 
			dataIndex: 'ctime',
			width: dp(140),
			hidden: true
		},		{
			header: t("Modified at"), 
			dataIndex: 'mtime',
			width: dp(140),
			hidden: true
		}
	]
	});
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	
	GO.postfixadmin.domainDialog = this.domainDialog = new GO.postfixadmin.DomainDialog();
	    			    		
	this.domainDialog.on('save', function(){   
		this.store.reload();	    			    			
	}, this);
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
  });
	
	
	if(GO.settings.modules.postfixadmin.write_permission)
	{
		config.tbar=new Ext.Toolbar({		
			cls:'go-head-tb',
			items: [{
				xtype:'htmlcomponent',
				html:t("Postfix admin", "postfixadmin"),
				cls:'go-module-title-tbar'
			},{
			iconCls: 'btn-add',							
			text: t("Add"),
			cls: 'x-btn-text-icon',
			handler: function(){				
	    	this.domainDialog.show();
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
		},t("Search")+': ', ' ',this.searchField]});
	}
	
	
	
	GO.postfixadmin.DomainsGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.domainDialog.show(record.data.id);
		
		}, this);
	
};

Ext.extend(GO.postfixadmin.DomainsGrid, GO.grid.GridPanel,{
	afterRender : function(){
		this.store.load();
		GO.postfixadmin.DomainsGrid.superclass.afterRender.call(this);
	}
});
