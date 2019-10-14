		
GO.addressbook.SelectAddresslistWindow = Ext.extend(Ext.Window, {
	
	initComponent : function(){
		
		this.title=t("Select an address list", "addressbook");
		
		this.grid = new GO.grid.GridPanel({
				layout: 'fit',
				border: false,
				height: 300,
				store: GO.addressbook.readableAddresslistsStore,
				paging: GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):true,
				view: new Ext.grid.GridView({
					autoFill: true,
					forceFit: true,
					emptyText: t("No items to display")
				}),
				cm: new Ext.grid.ColumnModel([
					{
						header: t("Name"),
						dataIndex: 'name'
					}
				]),
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: false
				})
			});
		
		this.grid.on('rowdblclick', function(grid, rowIndex){			
				
				var record = grid.store.getAt(rowIndex);
				
				var addresslist_id = record.data.id;
			
				this.fireEvent("select", this, addresslist_id);
//				this.grid.clearSelections();
				this.hide();
				
		}, this);
		
		
		this.title= t("selectAddresslist", "addressbook");
		this.layout='fit';
		this.modal=false;
		this.height=400;			
		this.width=400;
		this.closable=true;
		this.closeAction='hide';	
		this.items= this.panel = new Ext.Panel({
			autoScroll:true,
			items: this.grid,
			cls: 'go-form-panel'
		});
		this.buttons=[{
		text: t("Close"),
		handler: function(){
			this.hide();
		},
		scope:this
	}];
		
		GO.addressbook.SelectAddresslistWindow.superclass.initComponent.call(this);
		
		this.addEvents({"select":true});
	},
	
	show : function(){		
		if(!this.grid.store.loaded)
		{
			this.grid.store.load({
				callback:function(){
					this.show();
				},
				scope:this
			});
			return false;
		}
		
		GO.addressbook.SelectAddresslistWindow.superclass.show.call(this);
		
		if(this.grid.store.getCount()==0)
		{
			this.panel.body.update(t("You don't have any address list", "addressbook"));
		}
	}
});
