/* global go, Ext */

go.modules.community.addressbook.customfield.MultiContactDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),

	initFormItems: function () {
		let items = go.modules.community.addressbook.customfield.MultiContactDialog.superclass.initFormItems.call(this);

		items[1].items = items[1].items.concat({
			xtype: 'radiogroup',
			fieldLabel: t("Show"),
			name: "options.isOrganization",
			value: false,
			items: [
				{boxLabel: t("All"), inputValue: null},
				{boxLabel: t("Contacts"), inputValue: false},
				{boxLabel: t("Organizations"), inputValue: true}
			]

		}, {
			xtype: 'checkbox',
			fieldLabel: t("Allow new"),
			name: "options.allowNew",
			value: true
		}, {
			anchor: '100%',
			xtype: "chips",
			entityStore: "AddressBook",
			displayField: "name",
			name: "options.addressBookId",
			fieldLabel: t("Address books")
		});

		return items;
	}
});
