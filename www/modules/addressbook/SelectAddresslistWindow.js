		
GO.addressbook.SelectAddresslistWindow = Ext.extend(Ext.Window, {
	
	initComponent : function(){
		
		this.title=GO.addressbook.lang.selectMailingGroup;
		
		this.grid = new GO.grid.GridPanel({
				layout: 'fit',
				border: false,
				height: 300,
				store: GO.addressbook.readableAddresslistsStore,
				paging: true,
				view: new Ext.grid.GridView({
					autoFill: true,
					forceFit: true,
					emptyText: GO.lang.strNoItems
				}),
				cm: new Ext.grid.ColumnModel([
					{
						header: GO.lang['strName'],
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
		
		
		this.title= GO.addressbook.lang.selectAddresslist;
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
		text: GO.lang['cmdClose'],
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
			this.panel.body.update(GO.addressbook.lang.noMailingGroups);
		}
	}
});
