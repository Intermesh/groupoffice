go.modules.community.addressbook.GroupDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-group-dialog',
	title: t('Group'),
	entityStore: "AddressBookGroup",
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
