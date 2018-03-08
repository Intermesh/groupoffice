/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: CommentsGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.cms.CommentsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = GO.cms.lang.comments;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
	    url: GO.settings.modules.cms.url+ 'json.php',
	    baseParams: {
	    	task: 'comments'
	    	},
	    root: 'results',
	    id: 'id',
	    totalProperty:'total',
	    fields: ['id','file_id','user_name','name','comments','ctime'],
	    remoteSort: true
	});
	
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	   		{
			header: GO.cms.lang.fileId, 
			dataIndex: 'file_id'
		},		{
			header: GO.lang.strOwner, 
			dataIndex: 'user_name',
		  sortable: false
		},		{
			header: GO.lang.strName, 
			dataIndex: 'name'
		},		{
			header: GO.cms.lang.comments, 
			dataIndex: 'comments'
		},		{
			header: GO.lang.strCtime, 
			dataIndex: 'ctime',
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
	
	
	this.commentDialog = new GO.cms.CommentDialog();
	    			    		
		this.commentDialog.on('save', function(){   
			this.store.reload();	    			    			
		}, this);
	
	
	config.tbar=[{
			iconCls: 'btn-add',							
			text: GO.lang['cmdAdd'],
			cls: 'x-btn-text-icon',
			handler: function(){
				
	    	this.commentDialog.show();
	    	
	    	
	    	
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
	
	
	
	GO.cms.CommentsGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.commentDialog.show(record.data.id);
		
		}, this);
	
};

Ext.extend(GO.cms.CommentsGrid, GO.grid.GridPanel,{
	
});