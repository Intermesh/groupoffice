go.modules.community.addressbook.AddressBookDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-addressbook-dialog',
	title: t('Address book'),
	entityStore: "AddressBook",
	//autoHeight: true,
	width: dp(600),
	height: dp(600),

	initFormItems: function () {

		this.addPanel(new go.permissions.SharePanel({
			title: t("Permissions")
		}));

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
