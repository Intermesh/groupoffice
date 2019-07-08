go.modules.community.addressbook.CustomFieldSetDialog = Ext.extend(go.customfields.FieldSetDialog, {
	stateId: 'addressbook-custom-field-set-dialog',
	
	initFormItems: function () {
		var items = go.modules.community.addressbook.CustomFieldSetDialog.superclass.initFormItems.call(this);

		items[0].items = items[0].items.concat([
			{
				xtype: 'radiogroup',
				fieldLabel: t("Enable for"),
				name: "enableFor",
				submit: false,
				value: "all",
				items: [
					{boxLabel: t("All"), inputValue: "all"},
					{boxLabel: t("Contacts"), inputValue: 'contacts'},
					{boxLabel: t("Organizations"), inputValue: 'organizations'}
				]

			}, {
				xtype: "checkbox",
				name: "enableAddressBookFilter",
				boxLabel: t("Only show this field set for selected address books"),
				hideLabel: true,
				submit: false,
				listeners: {
					check: function (f, checked) {
						this.formPanel.getForm().findField("filter.addressBookId").setDisabled(!checked);
					},
					scope: this
				}
			},
			{
				anchor: '100%',
				disabled: true,
				xtype: "chips",
				entityStore: "AddressBook",
				displayField: "name",
				name: "filter.addressBookId",
				fieldLabel: t("Address books")
			}
		]);
		
		return items;
	},

	onLoad: function () {
		this.evalEnableFor();

		return go.modules.community.addressbook.CustomFieldSetDialog.superclass.onLoad.call(this);
	},

	onBeforeSubmit: function () {
		if (!this.formPanel.values.filter) {
			this.formPanel.values.filter = {};
		}
		
		switch (this.formPanel.getForm().findField('enableFor').getValue()) {
			case 'contacts':
				this.formPanel.values.filter.isOrganization = false;
				break;

			case 'organizations':
				this.formPanel.values.filter.isOrganization = true;
				break;

			default:
				delete this.formPanel.values.filter.isOrganization;
				break;

		}


		var enableAbFilter = this.formPanel.getForm().findField("enableAddressBookFilter").getValue();

		if (!enableAbFilter) {
			delete this.formPanel.values.filter.addressBookId;
		} else
		{
			//this.formPanel.values.filter.addressBookId = this.formPanel.getForm().findField("addressBooks").getValue().column("id");
		}

		return go.modules.community.addressbook.CustomFieldSetDialog.superclass.onBeforeSubmit.call(this);
	},

	evalEnableFor: function () {
		var enableFor = this.formPanel.getForm().findField('enableFor');

		this.formPanel.getForm().findField("enableAddressBookFilter").setValue(!!this.formPanel.entity.filter.addressBookId);

		//go.Db.store("AddressBook").get(this.formPanel.entity.filter.addressBookId, function (addressBooks) {
			//this.formPanel.getForm().findField("addressBooks").setValue(this.formPanel.entity.filter.addressBookId);
		//}, this);

		switch (this.formPanel.entity.filter.isOrganization) {
			case true:
				enableFor.setValue("organizations");
				break;

			case false:
				enableFor.setValue("contacts");
				break;

			default:
				enableFor.setValue("all");
		}
	}
});

