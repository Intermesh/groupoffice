
go.modules.community.addressbook.BirthdaysPortletSettingsDialog = Ext.extend(go.form.Dialog, {
	title: t("Address books"),
	entityStore: "User",
	width: dp(500),
	height: dp(500),
	modal: true,
	collapsible: false,
	maximizable: false,
	layout: 'fit',
	showCustomfields: false,

	initFormItems: function() {
		var items = [
			new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						layout: "form",
						items: [
							this.addressBookMultiSelect = new go.form.multiselect.Field({
								valueIsId: true,
								idField: 'addressBookId',
								displayField: 'name',
								entityStore: 'AddressBook',
								name: 'birthdayPortletAddressBooks',
								hideLabel: true,
								emptyText: t("Please select..."),
								pageSize: 50,
								fields: ['id', 'name'],
								storeBaseParams: {
									filter: {
										permissionLevel: go.permissionLevels.write
									}
								}
							})
						]
					}
				]
			})
		];

		return items;
	}
});
