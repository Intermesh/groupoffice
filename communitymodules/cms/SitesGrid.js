/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SitesGrid.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.SitesGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.border=false;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.settings.modules.cms.url+ 'json.php',
	    baseParams: {
	    	task: 'sites'
	    	},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','user_name','acl_write','allow_properties','domain','webmaster','publish_style','publish_path','template_id','root_folder_id','start_file_id','language','name'],
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
	
	
	this.siteDialog = new GO.cms.SiteDialog();
	    			    		
		this.siteDialog.on('save', function(){   
			this.store.reload();	    			    			
		}, this);
	
	
	config.tbar=[{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				
	    	this.siteDialog.show();
	    	
	    	
	    	
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
	
	
	
	GO.cms.SitesGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.siteDialog.show(record.data.id);
		
		}, this);
	
};

Ext.extend(GO.cms.SitesGrid, GO.grid.GridPanel,{
	
	loaded : false,
	
	afterRender : function()
	{
		GO.cms.SitesGrid.superclass.afterRender.call(this);
		
		if(this.isVisible())
		{
			this.onGridShow();
		}
	},
	
	onGridShow : function(){
		if(!this.loaded && this.rendered)
		{
			this.store.load();
			this.loaded=true;
		}
	}
	
});