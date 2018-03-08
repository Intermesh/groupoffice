/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AnnouncementsGrid.js 16251 2013-11-15 08:39:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.summary.AnnouncementsGrid = function(config){
	if(!config)
	{
		config = {};
	}
	
	config.border=false;
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		url: GO.url('summary/announcement/store'),
		baseParams: {
		},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','user_name','due_time','ctime','mtime','title'],
		remoteSort: true
	});
	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
	{
		header: GO.summary.lang.title,
		dataIndex: 'title'
	},{
		header: GO.lang.strOwner,
		dataIndex: 'user_name',
		sortable: false
	},{
		header: GO.summary.lang.dueTime,
		dataIndex: 'due_time'
	},{
		header: GO.lang.strCtime,
		dataIndex: 'ctime',
		width:110
	},{
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

	this.announcementDialog = new GO.summary.AnnouncementDialog();
	this.announcementDialog.on('save', function(){
		this.store.reload();
	}, this);
	
	config.tbar=[{
		iconCls: 'btn-add',
		text: GO.lang['cmdAdd'],
		cls: 'x-btn-text-icon',
		handler: function(){
			this.announcementDialog.show();
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
	GO.summary.AnnouncementsGrid.superclass.constructor.call(this, config);
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		this.announcementDialog.show(record.data.id);
	}, this);
};
Ext.extend(GO.summary.AnnouncementsGrid, GO.grid.GridPanel,{

	
	});
