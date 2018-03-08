GO.addressbook.ManageAddressbooksGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = GO.addressbook.lang.addressbooks;
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
	  	header: GO.lang['strName'], 
	  	dataIndex: 'name'
	  },
	  {
	  	header: GO.addressbook.lang['cmdOwner'], 
	  	dataIndex: 'user_name' ,
	  	sortable: false
	  }
	]
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: GO.addressbook.lang.noAddressbooks		
	}),
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.tbar=[
			{ 
				iconCls: 'btn-add', 
				text: GO.lang.cmdAdd, 
				cls: 'x-btn-text-icon', 
				handler: function(){
					this.addressbookDialog.show();
				},
				disabled: !GO.settings.modules.addressbook.write_permission,
				scope: this
			},
			{
				iconCls: 'btn-delete', 
				text: GO.lang.cmdDelete, 
				cls: 'x-btn-text-icon', 
				handler: function(){
					this.deleteSelected({
						success: function() {
							GO.addressbook.readableAddressbooksStore.load();
						}
					});
				}, 
				disabled: !GO.settings.modules.addressbook.write_permission,
				scope: this
			},'-',new GO.form.SearchField({
				store: config.store,
				width:150
			})
		];
	
	GO.addressbook.ManageAddressbooksGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick', function(grid, rowIndex){
		var record = grid.getStore().getAt(rowIndex);	
		
		this.addressbookDialog.show(record.id);
		
		}, this);

	// Moved here from Stores.js to let this event only fire from within the
	// administration grid.
//	this.store.on('load', function(){
//		GO.addressbook.readableAddressbooksStore.load();
//	}, this);
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
