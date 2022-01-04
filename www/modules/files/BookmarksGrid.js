GO.files.BookmarksGrid = function(config) {
	
	var config = config || {};
	
	config.title = t("Favorites", "files");
	config.collapsible=true;
	config.stateId='fs-bookmarks';
	config.layout = 'fit';
	config.split = true;
	config.paging = false;
	config.cls = 'go-grid3-hide-headers';
	config.autoHeight = true;
	config.store = new GO.data.JsonStore({
		url:GO.url("files/bookmark/store"),
		id: 'folder_id',
		fields:["folder_id","name"],
		remoteSort:true
	});
	config.columns = [{
		header:t("Name"),
		renderer:function(v, meta, r){
			return '<i class="icon" style="color: rgba(0, 0, 0, 0.54);">folder</i> &nbsp;'+v;
		},
		dataIndex: 'name',
//					renderer:function(v, metaData,record){
//						return '<div class="go-grid-icon filetype filetype-'+record.get("extension")+'">'+v+'</div>';
//					},
		sortable:true
	}];
	config.view = new  Ext.grid.GridView({
		autoFill:true,
		forceFit:true
	});
	config.sm = new Ext.grid.RowSelectionModel();
	config.loadMask = true;
	
// 	config.bbar = new GO.SmallPagingToolbar({
// //			items:[this.searchField = new GO.form.SearchField({
// //				store: config.store,
// //				width:120,
// //				emptyText: t("Search")
// //			})],
// 			store:config.store,
// 			pageSize:GO.settings.config.nav_page_size
// 		})
	
	GO.files.BookmarksGrid.superclass.constructor.call(this,config);
	
	this.addEvents({
		'bookmarkClicked' : true,
		'delete' : true
	});
	
	this.on('rowcontextmenu',function(grid,rowIndex,event){		
		this._clickedBookmarkRecord = grid.getStore().getAt(rowIndex);
		if (GO.util.empty(this._contextMenu))
			this._createContextMenu();
		this._contextMenu.showAt(event.getXY());
	},this);
	
	this.on('rowclick',function(grid,rowIndex,event){
		this.fireEvent('bookmarkClicked', this, grid.getStore().getAt(rowIndex));
	},this);
	
};

Ext.extend(GO.files.BookmarksGrid,GO.grid.GridPanel,{
	
	_clickedBookmarkRecord : false,
	
	_createContextMenu : function() {
		this._contextMenu = new Ext.menu.Menu({
			shadow : 'frame',
			minWidth : 180,
			items : [{
				iconCls: 'ic-delete',
				text: t("Delete"),
				handler: function(){
					this._promptDelete(this._clickedBookmarkRecord);
				},
				scope: this
			}]
		});
	},
	
	_promptDelete : function(bookmarkRecord) {
		GO.deleteItems({
			url:GO.url('files/bookmark/delete'),
			params:{
				folder_id: bookmarkRecord.data['folder_id']
			},
			count:1,
			callback:function(responseParams){
				if(responseParams.success) {
					this.store.load();
					this.fireEvent('delete', this, bookmarkRecord );
				}
				this._clickedBookmarkRecord = false;
			},
			scope:this
		});
	}
	
});
