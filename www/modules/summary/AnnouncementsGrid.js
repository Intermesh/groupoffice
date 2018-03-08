/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AnnouncementsGrid.js 22112 2018-01-12 07:59:41Z mschering $
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
		header: t("Title", "summary"),
		dataIndex: 'title'
	},{
		header: t("Owner"),
		dataIndex: 'user_name',
		sortable: false
	},{
		header: t("Show until", "summary"),
		dataIndex: 'due_time'
	},{
		header: t("Created at"),
		dataIndex: 'ctime',
		width: dp(140)
	},{
		header: t("Modified at"),
		dataIndex: 'mtime',
		width: dp(140)
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

	this.announcementDialog = new GO.summary.AnnouncementDialog();
	this.announcementDialog.on('save', function(){
		this.store.reload();
	}, this);
	
	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function(){
			this.announcementDialog.show();
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
	}];
	GO.summary.AnnouncementsGrid.superclass.constructor.call(this, config);
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		this.announcementDialog.show(record.data.id);
	}, this);
};
Ext.extend(GO.summary.AnnouncementsGrid, GO.grid.GridPanel,{

	
	});
