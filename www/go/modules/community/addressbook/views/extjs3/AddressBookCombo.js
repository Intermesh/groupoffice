go.modules.community.addressbook.AddresBookCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Addres Book"),
	hiddenName: 'addressBookId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: go.Stores.get("AddressBook"),
				baseParams: {
					filter: {
							permissionLevel: GO.permissionLevels.write
					}
				}
			})
		});
		
		go.modules.community.addressbook.AddresBookCombo.superclass.initComponent.call(this);

	}
});

Ext.reg("addressbookcombo", go.modules.community.addressbook.AddresBookCombo);