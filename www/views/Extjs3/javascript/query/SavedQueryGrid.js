GO.query.SavedQueryGrid = function(config) {
	
	config = config || {};
	
	config.width = 250;
	
	config.store = new GO.data.JsonStore({
		url : GO.url('advancedSearch/store'),
		root : 'results',
		baseParams:{
			model_name: config.modelName
		},
		totalProperty : 'total',
		fields : ['id','name','acl_id','user_id','data'],
		remoteSort : true
	});
	
	config.cm=new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns: [{
			dataIndex : 'id',
			hidden: true,
			id: 'id'
		},
		{
			header: t("Saved queries"),
			dataIndex : 'name',
			hidden: false,
			width: '230',
			id: 'name'
		}]
	});
	
	config.autoExpandColumn = 'name';
	
	config.view=new Ext.grid.GridView({
		emptyText: t("No items to display")	
	});
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.listeners={
		render:function(){
			this.store.load();
		},
		scope:this
	}
	
	config.tbar = [
		{xtype:'tbtitle',text: t("Saved queries")},
		{xtype:'tbsearch', store: config.store}
	];
	
	config.paging = true;
	config.bbar = new GO.SmallPagingToolbar({
		store:config.store,
		pageSize:GO.settings.config.nav_page_size
	});

	GO.query.SavedQueryGrid.superclass.constructor.call(this, config);
	
	this.queryPanel.on('createNew',function(){
		this.queryId=0;
	},this);
	
	this.on('rowdblclick',function(grid,rowId,e){
			var record = grid.store.getAt(rowId);
			this.queryId = record.data.id;
			
			if (!GO.util.empty(record)) {
				var data = Ext.decode(record.data.data);
				this.queryPanel.clear();
				Ext.each(data, function(item) {
					var rec = new this.queryPanel.criteriaRecord(item);
					var count = this.queryPanel.criteriaStore.getCount();
					this.queryPanel.criteriaStore.insert(count, rec);
				}, this);
			}
			this.queryPanel.setQueryTitel(record.data.name);
		},this);
	
	this.on('contextmenu',function(eventObject,target,object){
		if (!this.queryContextMenu)
			this.queryContextMenu = new GO.query.QueryContextMenu();
		
		this.queryContextMenu.showAt(eventObject.xy);
		this.queryContextMenu.callingGrid = this;
	},this);
	
};

Ext.extend(GO.query.SavedQueryGrid,GO.grid.GridPanel,{
	
	queryId : 0,
	
	queryPanel : false,
	
	showSavedQueryDialog : function(queryId) {
		
		if(!queryId)
			queryId=this.queryId;
		
		if (!this.savedQueryDialog)
			this.savedQueryDialog = new GO.query.SavedQueryDialog({
				savedQueryGrid:this
			});
		
		this.savedQueryDialog.show(
			queryId, {
				'model_name' : this.modelName
			}
		);
	}
});
