go.modules.community.addressbook.AddressBookDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-addressbook-dialog',
	title: t('Address book'),
	entityStore: "AddressBook",
	width: dp(800),
	height: dp(600),

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel());

		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}]
			}
		];
	}
});
