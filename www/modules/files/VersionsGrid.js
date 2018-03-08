/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: VersionsGrid.js 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.files.VersionsGrid = function(config) {

	if (!config) {
		config = {};
	}

	config.title = GO.files.lang.olderVersions;
	config.layout = 'fit';
	config.autoScroll = true;
	config.split = true;
	config.store = new GO.data.JsonStore({
		url : GO.url("files/version/store"),
		fields : ['id', 'mtime','user_name','version','size_bytes'],
		remoteSort : true,
		id:'id'
	});
	config.store.setDefaultSort('mtime', 'desc');
		
	config.paging = true;
	var columnModel = new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[{
				header:GO.files.lang.shortVersion,
				dataIndex : 'version',
				width:50,
				align:'right'
		},{
			header : GO.lang['strOwner'],
			dataIndex : 'user_name',
			sortable : false,
			id:'name'
		},{
			header : GO.lang['strSize'],
			dataIndex : 'size_bytes',
			sortable : true,
			renderer: GO.util.format.fileSize
		}, {
			header : GO.lang.strMtime,
			dataIndex : 'mtime',
			width:100
		}]
	});
	
	config.cm = columnModel;
	
	config.autoExpandColumn='name';

	config.view = new Ext.grid.GridView({
		emptyText : GO.lang['strNoItems']
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;

	GO.files.VersionsGrid.superclass.constructor.call(this, config);

	this.on('rowdblclick', function(grid, rowIndex) {
		var record = grid.getStore().getAt(rowIndex);
		window.open(GO.url("files/version/download",{id:record.id}));
	}, this);

};

Ext.extend(GO.files.VersionsGrid, GO.grid.GridPanel, {

	onShow : function() {
		GO.files.VersionsGrid.superclass.onShow.call(this);
		this.store.load();
	},

	setFileID : function(file_id) {
		this.store.baseParams.file_id = file_id
		this.store.loaded = false;
	}

});