/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: FilesGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.FilesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = GO.cms.lang.files;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.settings.modules.cms.url+ 'json.php',
	    baseParams: {
	    	task: 'files',
	    	site_id: 0
	    	
	    	},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','folder_id','extension','size','ctime','mtime','name','content','auto_meta','title','description','keywords','priority','hot_item','hot_item_text','template_item_id','acl','registered_comments','unregistered_comments'],
	    remoteSort: true
	});
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	   		{
			header: GO.cms.lang.folderId, 
			dataIndex: 'folder_id'
		},		{
			header: GO.cms.lang.extension, 
			dataIndex: 'extension'
		},		{
			header: GO.cms.lang.size, 
			dataIndex: 'size'
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
			header: GO.cms.lang.content, 
			dataIndex: 'content'
		},		{
			header: GO.cms.lang.autoMeta, 
			dataIndex: 'auto_meta'
		},		{
			header: GO.cms.lang.title, 
			dataIndex: 'title'
		},		{
			header: GO.lang.strDescription, 
			dataIndex: 'description'
		},		{
			header: GO.cms.lang.keywords, 
			dataIndex: 'keywords'
		},		{
			header: GO.cms.lang.priority, 
			dataIndex: 'priority'
		},		{
			header: GO.cms.lang.hotItem, 
			dataIndex: 'hot_item'
		},		{
			header: GO.cms.lang.hotItemText, 
			dataIndex: 'hot_item_text'
		},		{
			header: GO.cms.lang.templateItemId, 
			dataIndex: 'template_item_id'
		},		{
			header: GO.cms.lang.acl, 
			dataIndex: 'acl'
		},		{
			header: GO.cms.lang.registeredComments, 
			dataIndex: 'registered_comments'
		},		{
			header: GO.cms.lang.unregisteredComments, 
			dataIndex: 'unregistered_comments'
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
	
	
	this.fileDialog = new GO.cms.FileDialog();
	    			    		
		this.fileDialog.on('save', function(){   
			this.store.reload();	    			    			
		}, this);
	
	
	config.tbar=[{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				
	    	this.fileDialog.show();
	    	this.fileDialog.formPanel.form.setValues({site_id: this.store.baseParams.site_id});
	    	
	    	
	    	
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
	
	
	
	GO.cms.FilesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.fileDialog.show(record.data.id);
		
		}, this);
	
};

Ext.extend(GO.cms.FilesGrid, GO.grid.GridPanel,{
	
});