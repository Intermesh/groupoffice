go.modules.community.addressbook.AddressBookDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-addressbook-dialog',
	title: t('Address book'),
	entityStore: go.Stores.get("AddressBook"),
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						required: true
					}]
			}
		];
	}
});
