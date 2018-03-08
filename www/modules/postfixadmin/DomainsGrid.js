/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: DomainsGrid.js 16251 2013-11-15 08:39:41Z mschering $
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
			header: GO.postfixadmin.lang.domain, 
			dataIndex: 'domain'
		},	{
			header: GO.lang.strOwner, 
			dataIndex: 'user_name',
		  sortable: false
		},		{
			header: GO.lang.strDescription, 
			dataIndex: 'description'
		},		{
			header: GO.postfixadmin.lang.aliases, 
			dataIndex: 'alias_count',
			sortable:false
		},		{
			header: GO.postfixadmin.lang.mailboxes, 
			dataIndex: 'mailbox_count',
			sortable:false
		},			{
			header: GO.postfixadmin.lang.quota, 
			dataIndex: 'quota',
			sortable:false
		},	{
			header: GO.postfixadmin.lang.usedQuota, 
			dataIndex: 'used_quota',
			sortable:false
		},			{
			header: GO.postfixadmin.lang.usage,
			dataIndex: 'usage',
			sortable:false
		},	{
			header: GO.postfixadmin.lang.active, 
			dataIndex: 'active'
		},		{
			header: GO.postfixadmin.lang.backupmx, 
			dataIndex: 'backupmx'
		},	{
			header: GO.lang.strCtime, 
			dataIndex: 'ctime',
			width:110
		},		{
			header: GO.lang.strMtime, 
			dataIndex: 'mtime',
			width:110
		}
	]
	});
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.lang['strNoItems']		
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
				html:GO.postfixadmin.lang.name,
				cls:'go-module-title-tbar'
			},{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){				
	    	this.domainDialog.show();
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
		},GO.lang['strSearch']+': ', ' ',this.searchField]});
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