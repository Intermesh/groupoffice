GO.files.RecentFilesGrid = Ext.extend(GO.grid.GridPanel,{	
	constructor : function(config){
		
		config=config||{};
		
		config.store = new GO.data.JsonStore({
			url:GO.url("files/file/recent"),
			id: 'id',
			fields:["id","path","name"],
			remoteSort:true
		});
		
		var reader = new Ext.data.JsonReader({
			root: 'results',
			totalProperty: 'total',
			fields:["id","path","name","weekday","mtime","extension"],
			id: 'id'
		});
		
		config.store = new Ext.data.GroupingStore({
			url: GO.url("files/file/recent"),
			reader: reader,
			groupField: 'weekday',
			remoteGroup:true,
			remoteSort:true
		});
		
		config.viewConfig = {'forceFit':true,'autoFill':true};
		
		config.view=new Ext.grid.GroupingView({
			scrollOffset: 2,
			hideGroupedColumn:true,
			emptyText: GO.lang.noItems
		})
		
		config.autoExpandColumn='path';
		config.columns = [{
			id:'path',
			dataIndex:'path',
			header:GO.files.lang.path,
			renderer:function(v, meta, r){
				var cls = 'filetype filetype-'+r.get('extension');
				if(r.get('locked_user_id')>0)
					v = '<div class="fs-grid-locked">'+v+'</div>';

				return '<div class="go-grid-icon '+cls+'" style="float:left;">'+v+'</div>';
			}
		},{
			id:'name',
			dataIndex:'name',
			width:180,
			header:GO.lang.strName
//			renderer:function(v, meta, r){
//				var cls = 'filetype filetype-'+r.get('extension');
//				if(r.get('locked_user_id')>0)
//					v = '<div class="fs-grid-locked">'+v+'</div>';
//
//				return '<div class="go-grid-icon '+cls+'" style="float:left;">'+v+'</div>';
//			}
		},{
			header:GO.lang.strMtime,
			dataIndex:'mtime',
			width:110
		},
			{
				dataIndex:'weekday',
				header:GO.lang.strDay
			}];
		
		config.listeners={
			render:function(){
				this.store.load();
			},
			rowdblclick:function(grid, rowClicked, e){
				var selectionModel = grid.getSelectionModel();
				var record = selectionModel.getSelected();
				
				GO.linkHandlers["GO\\Files\\Model\\File"].call(this, record.id);
				
			},
			scope:this
		}
		
		config.autoHeight=true;
		
		config.bbar = new Ext.PagingToolbar({
        store: config.store,
				pageSize: parseInt(GO.settings['max_rows_list']),
				displayInfo: true,
				displayMsg: GO.lang['displayingItems'],
				emptyMsg: GO.lang['strNoItems']
//        displayInfo: true,
//        displayMsg: 'Displaying topics {0} - {1} of {2}',
//        emptyMsg: "No topics to display"
    });
		
		GO.files.RecentFilesGrid.superclass.constructor.call(this,config);
	
	}
});