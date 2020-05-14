GO.files.RecentFilesGrid = Ext.extend(GO.grid.GridPanel,{	
	constructor : function(config){
		
		config=config||{};

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
			emptyText: t("noItems")
		})
		
		config.autoExpandColumn='path';
		config.columns = [{
			id:'path',
			dataIndex:'path',
			header:t("Path", "files"),
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
			header:t("Name")
//			renderer:function(v, meta, r){
//				var cls = 'filetype filetype-'+r.get('extension');
//				if(r.get('locked_user_id')>0)
//					v = '<div class="fs-grid-locked">'+v+'</div>';
//
//				return '<div class="go-grid-icon '+cls+'" style="float:left;">'+v+'</div>';
//			}
		},{
			header:t("Modified at"),
			dataIndex:'mtime',
			xtype: "datecolumn"
		},
			{
				dataIndex:'weekday',
				header:t("Day")
			}];
		
		config.listeners={
			render:function(){
				this.store.load();
			},
			rowdblclick:function(grid, rowClicked, e){
				var selectionModel = grid.getSelectionModel();
				var record = selectionModel.getSelected();
				
				go.Router.goto("#file/" + record.id);
				
				
			},
			scope:this
		}
		
		config.autoHeight=true;
		
		config.bbar = new Ext.PagingToolbar({
        store: config.store,
				pageSize: parseInt(GO.settings['max_rows_list']),
				displayInfo: true,
				displayMsg: t("Displaying items {0} - {1} of {2}"),
				emptyMsg: t("No items to display")
//        displayInfo: true,
//        displayMsg: 'Displaying topics {0} - {1} of {2}',
//        emptyMsg: "No topics to display"
    });
		
		GO.files.RecentFilesGrid.superclass.constructor.call(this,config);
	
	}
});
