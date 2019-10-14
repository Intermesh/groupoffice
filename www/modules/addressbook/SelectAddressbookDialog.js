		
GO.addressbook.SelectAddressbookWindow = Ext.extend(Ext.Window, {
	
	initComponent : function(){
		this.addEvents({'select' : true});	
		
		
		this.title=t("Select an addressbook", "addressbook");
		
		this.list = new GO.grid.SimpleSelectList({
			store : new GO.data.JsonStore({
					url: GO.url('addressbook/addressbook/store'),
					baseParams: {
						'permissionLevel' : GO.permissionLevels.read,
						start:0,
						limit:0

						},
					root: 'results', 
					totalProperty: 'total', 
					id: 'id',
					fields: GO.addressbook.addressbooksStoreFields,
					remoteSort: true
				})
				//store: GO.addressbook.readableAddressbooksStore 
			});
		
		this.list.on('click', function(dataview, index){			
				
				var addressbook_id = dataview.store.data.items[index].id;
			
				this.fireEvent('select', addressbook_id);			
				this.hide();
				
		}, this);
		
		this.on('show', function(){
			this.list.store.load();
		}, this);
		
		this.title= t("Select an addressbook", "addressbook");
		this.layout='fit';
		this.modal=false;
		this.height=400;			
		this.width=400;
		this.closable=true;
		this.closeAction='hide';	
		this.items= this.panel = new Ext.Panel({
			autoScroll:true,
			items: this.list,
			cls: 'go-form-panel'
		});
		this.buttons=[{
			text: t("Close"),
			handler: function(){
				this.hide();
			},
			scope:this
		}];
		
		GO.addressbook.SelectAddressbookWindow.superclass.initComponent.call(this);
		
	}
});
