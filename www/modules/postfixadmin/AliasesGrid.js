/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AliasesGrid.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.postfixadmin.AliasesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("Aliases", "postfixadmin");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.url('postfixadmin/alias/store'),
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','address','goto','ctime','mtime','active'],
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
			header: t("Address", "postfixadmin"), 
			dataIndex: 'address'
		},		{
			header: t("Goto", "postfixadmin"), 
			dataIndex: 'goto'
		},		{
			header: t("Created at"), 
			dataIndex: 'ctime',
				xtype: "datecolumn"
		},		{
			header: t("Modified at"), 
			dataIndex: 'mtime',
				xtype: "datecolumn"
		},		{
			header: t("Active", "postfixadmin"), 
			dataIndex: 'active'
		}
	]
	});

	columnModel.defaultSortable = true;
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	
	this.aliasDialog = new GO.postfixadmin.AliasDialog();
	    			    		
		this.aliasDialog.on('save', function(){   
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
	    	this.aliasDialog.show(0,{loadParams:{domain_id:this.store.baseParams.domain_id}});	    	
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
	
	
	
	GO.postfixadmin.AliasesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);			
		this.aliasDialog.show(record.data.id,{loadParams:{domain_id:this.store.baseParams.domain_id}});
		}, this);
	
};

Ext.extend(GO.postfixadmin.AliasesGrid, GO.grid.GridPanel,{
	onShow : function(){
		//if(!this.store.loaded)
		//{
			this.store.load();
		//}
		
		GO.postfixadmin.AliasesGrid.superclass.onShow.call(this);
	},
	
	setDomainId : function(domain_id)
	{
		this.store.baseParams.domain_id=domain_id;
		this.store.loaded=false;
		this.aliasDialog.formPanel.baseParams.domain_id=domain_id;
		this.setDisabled(domain_id<1);
	}
});
