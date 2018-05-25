GO.calendar.GroupsGrid = function(config){

	if(!config)
	{
		config = {};
	}

	config.title = t("Resource groups", "calendar");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.paging=true;
    
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
			header: t("Name"),
			dataIndex: 'name'
		},{
			header: t("Owner"),
			dataIndex: 'user_name',
			sortable: false
		}]
	});

	
	config.cm=columnModel;
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});

	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
    
	config.tbar=[{
		iconCls: 'btn-add',
		text: t("Add"),
		cls: 'x-btn-text-icon',
		handler: function()
		{
			GO.calendar.groupDialog.show();
		},
		scope: this
	},{
		iconCls: 'btn-delete',
		text: t("Delete"),
		cls: 'x-btn-text-icon',
		handler: function()
		{
			this.deleteSelected();
		},
		scope: this
	},
	'-'
	,
		this.searchField = new GO.form.SearchField({
			store: config.store,
			width:150,
			emptyText: t("Search")
		})
	];

	GO.calendar.GroupsGrid.superclass.constructor.call(this, config);

	this.on('rowdblclick', function(grid, rowIndex)
	{
		var record = grid.getStore().getAt(rowIndex);	
		GO.calendar.groupDialog.show(record.data.id);
	}, this);


	this.on('show', function(){
		if(!this.store.loaded)
		{
			this.store.load();
		}
	},this, {
		single:true
	});

};

Ext.extend(GO.calendar.GroupsGrid, GO.grid.GridPanel);
