GO.addressbook.ManageAddressbooksGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("Address books", "addressbook");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = GO.addressbook.writableAddressbooksStore	
	
	config.paging=true;

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:[
		{
	  	header: 'ID', 
	  	dataIndex: 'id',
			hidden:true
	  },
	  {
	  	header: t("Name"), 
	  	dataIndex: 'name'
	  },
	  {
	  	header: t("Owner", "addressbook"), 
	  	dataIndex: 'user_name' ,
	  	sortable: false
	  }
	]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("There are no address books", "addressbook")		
	}),
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.tbar=[{ 
		iconCls: 'ic-add', 
		text: t("Add"),
		handler: function(){
			this.addressbookDialog.show();
		},
		disabled: !GO.settings.modules.addressbook.write_permission,
		scope: this
	},
	{
		iconCls: 'ic-delete', 
		text: t("Delete"),
		handler: function(){
			this.deleteSelected({
				success: function() {
					GO.addressbook.readableAddressbooksStore.load();
				}
			});
		}, 
		disabled: !GO.settings.modules.addressbook.write_permission,
		scope: this
	},'->',{
		xtype: 'tbsearch',
		store: config.store
	}];
	
	GO.addressbook.ManageAddressbooksGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.addressbookDialog.show(record.id);
		
	}, this);
};


Ext.extend(GO.addressbook.ManageAddressbooksGrid, GO.grid.GridPanel,{
	
	afterRender : function()
	{
		GO.addressbook.ManageAddressbooksGrid.superclass.afterRender.call(this);
		
		if(!GO.addressbook.writableAddressbooksStore.loaded)
		{
			GO.addressbook.writableAddressbooksStore.load();
		}

		this.addressbookDialog = new GO.addressbook.AddressbookDialog();
		this.addressbookDialog.on('save', function(){
			GO.addressbook.writableAddressbooksStore.load();
			GO.addressbook.readableAddressbooksStore.load();
		});
	},
	
	onShow : function(){
		GO.addressbook.ManageAddressbooksGrid.superclass.onShow.call(this);
		if(!GO.addressbook.writableAddressbooksStore.loaded)
		{
			GO.addressbook.writableAddressbooksStore.load();
		}
	}
	
});
