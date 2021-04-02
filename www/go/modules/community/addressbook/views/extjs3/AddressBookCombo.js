/* global Ext, go, GO */

go.modules.community.addressbook.AddresBookCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Address book"),
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
	store: {
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "AddressBook",
		filters: {
			default: {
					permissionLevel: go.permissionLevels.write
			}
		}
	}
});

Ext.reg("addressbookcombo", go.modules.community.addressbook.AddresBookCombo);
