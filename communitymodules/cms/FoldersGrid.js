/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: FoldersGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.FoldersGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = GO.cms.lang.folders;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.settings.modules.cms.url+ 'json.php',
	    baseParams: {
	    	task: 'folders',
	    	site_id: 0
	    	
	    	},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','parent_id','ctime','mtime','name','disabled','priority','multipage','template_item_id','acl'],
	    remoteSort: true
	});
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	   		{
			header: GO.cms.lang.parentId, 
			dataIndex: 'parent_id'
		},		{
			header: GO.lang.strCtime, 
			dataIndex: 'ctime',
			width:110
		},		{
			header: GO.lang.strMtime, 
			dataIndex: 'mtime',
			width:110
		},		{
			header: GO.lang.strName, 
			dataIndex: 'name'
		},		{
			header: GO.cms.lang.disabled, 
			dataIndex: 'disabled'
		},		{
			header: GO.cms.lang.priority, 
			dataIndex: 'priority'
		},		{
			header: GO.cms.lang.multipage, 
			dataIndex: 'multipage'
		},		{
			header: GO.cms.lang.templateItemId, 
			dataIndex: 'template_item_id'
		},		{
			header: GO.cms.lang.acl, 
			dataIndex: 'acl'
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
	
	
	this.folderDialog = new GO.cms.FolderDialog();
	    			    		
		this.folderDialog.on('save', function(){   
			this.store.reload();	    			    			
		}, this);
	
	
	config.tbar=[{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				
	    	this.folderDialog.show();
	    	this.folderDialog.formPanel.form.setValues({site_id: this.store.baseParams.site_id});
	    	
	    	
	    	
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
		}];
	
	
	
	GO.cms.FoldersGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.folderDialog.show(record.data.id);
		
		}, this);
	
};

Ext.extend(GO.cms.FoldersGrid, GO.grid.GridPanel,{
	
});