
go.modules.community.addressbook.BirthdaysPortletSettingsDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-birthday-portlet-settings-dialog',
	title: t("Address books"),
	entityStore: "User",
	width: dp(320),
	height: dp(200),
	modal: true,
	collapsible: false,
	maximizable: false,
	layout: 'fit',

	initFormItems: function() {
		var items = [
			new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						layout: "form",
						items: [
							this.addressBookMultiSelect = new go.form.multiselect.Field({
								idField: 'addressBookId',
								displayField: 'name',
								entityStore: 'AddressBook',
								name: 'birthdayPortletAddressBooks',
								hideLabel: true,
								valueIsId: false,
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
	},
});
