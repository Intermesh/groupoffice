GO.addressbook.AddresslistsMultiSelectGrid = function(config) {
	var config = config || {};
	
	config.title = GO.addressbook.lang.filterMailings;
	config.loadMask = true;
	config.store = GO.addressbook.readableAddresslistsStore;
	config.allowNoSelection = true;
	
	Ext.applyIf(config,{
		region:'center'
	});
	
	Ext.apply(config, {		
		bbar: new GO.SmallPagingToolbar({
			items:[this.searchField = new GO.form.SearchField({
				store: config.store,
				width:120,
				emptyText: GO.lang.strSearch
			})],
			store:config.store,
			pageSize:GO.settings.config.nav_page_size
		})
	});
	
	GO.addressbook.AddresslistsMultiSelectGrid.superclass.constructor.call(this,config);
	
};

Ext.extend(GO.addressbook.AddresslistsMultiSelectGrid, GO.grid.MultiSelectGrid, {
	afterRender : function() {
		GO.addressbook.AddresslistsMultiSelectGrid.superclass.afterRender.call(this);
		
		var DDtarget = new Ext.dd.DropTarget(this.getView().mainBody, {
			ddGroup : 'AddressBooksDD',
			notifyDrop : this.onNotifyDrop.createDelegate(this)
		});	
	},
	
	onNotifyDrop : function(source, e, data)
	{	
		
		var selections = source.dragData.selections;
		var dropRowIndex = this.getView().findRowIndex(e.target);
		var list_id = this.getView().grid.store.data.items[dropRowIndex].id;
		var list_name = this.getView().grid.store.data.items[dropRowIndex].data.name;
		
		var contacts = [];
		var companies = [];
		
		for (var i=0; i<selections.length; i++) {
			
			var selection = selections[i];
			
			if ('name2' in selection.data && 'post_address' in selection.data)
				companies.push(selection.data.id);
			else
				contacts.push(selection.data.id);
			
		}
		
		Ext.Msg.show({
			title: GO.addressbook.lang['addToAddresslist'].replace('%s',list_name),
			msg: GO.addressbook.lang['addToAddresslistPrompt'].replace(/%s/g,list_name),
			buttons: Ext.Msg.YESNOCANCEL,
			scope: this,
			fn: function(btn) {
				if (btn!='cancel') {
					GO.request({
						url: 'addressbook/addresslist/add',
						params: {
							contacts : Ext.encode(contacts),
							companies : Ext.encode(companies),
							addresslistId : list_id,
							move : btn=='yes'
						},
						success: function(options, response, result)
						{
							Ext.Msg.alert(GO.lang['strSuccess'],GO.addressbook.lang['addAddresslistSuccess']);
						},
						scope: this
					})
				}
			}
		});
	}
});