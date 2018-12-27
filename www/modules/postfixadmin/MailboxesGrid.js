/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MailboxesGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.postfixadmin.MailboxesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("Mailboxes", "postfixadmin");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.url('postfixadmin/mailbox/store'),
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','username','name','quota','usage','ctime','mtime','active'],
	    remoteSort: true
	});
	
	config.disabled=true;
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	   		{
			header: t("Username", "postfixadmin"), 
			dataIndex: 'username'
		},		{
			header: t("Name"), 
			dataIndex: 'name'
		},	{
			header: t("Quota (MB)", "postfixadmin"), 
			dataIndex: 'quota'
		}, {
			header: t("Usage", "postfixadmin"),
			dataIndex: 'usage'
		}, 		{
			header: t("Created at"), 
			dataIndex: 'ctime',
			width: dp(140),
			hidden: true
		},		{
			header: t("Modified at"), 
			dataIndex: 'mtime',
			width: dp(140),
			hidden: true
		},		{
			header: t("Active", "postfixadmin"), 
			dataIndex: 'active'
		}
	]});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	
	this.mailboxDialog = new GO.postfixadmin.MailboxDialog();
	    			    		
		this.mailboxDialog.on('save', function(){   
			this.store.reload();	    			    			
		}, this);
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
  });
	
	config.tbar=[{
			iconCls: 'btn-add',							
			text: t("Add"),
			cls: 'x-btn-text-icon',
			handler: function(){				
	    	this.mailboxDialog.show(0, {
					loadParams:{domain_id:this.store.baseParams.domain_id
					}
				});
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
		},t("Search")+': ', ' ',this.searchField];

	config.listeners={
		show:function(){
			if(!this.store.loaded && this.store.baseParams.domain_id>0)
			{
				this.store.load();
			}
		},/*
		render:function(){
			if(!this.store.loaded && this.store.baseParams.domain_id>0)
			{
				this.store.load();
			}
		},*/
		scope:this
	}
	
	GO.postfixadmin.MailboxesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);			
		this.mailboxDialog.show(record.data.id,{loadParams:{domain_id:this.store.baseParams.domain_id}});
		}, this);
	
};

Ext.extend(GO.postfixadmin.MailboxesGrid, GO.grid.GridPanel,{
	
	
//	setDomainId : function(domain_id)
//	{
//		this.store.baseParams.domain_id=domain_id;
//		this.store.loaded=false;
//		this.mailboxDialog.setDomainId(domain_id);
//		this.setDisabled(domain_id<1);
//	}
});
