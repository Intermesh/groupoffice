go.modules.community.addressbook.CustomFieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-custom-field-set-dialog',
	title: t('Field set'),
	entityStore: go.Stores.get("FieldSet"),
	height: dp(400),
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [{
						xtype: "hidden",
						name: "entity",
						value: "Contact"
					},
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}, {
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
						
					},{
						xtype:"checkbox",
						name: "enableAddressBookFilter",
						boxLabel: t("Only show this field set for selected address books"),
						hideLabel: true,
						submit: false,
						listeners: {
							check: function(f, checked) {
								this.formPanel.getForm().findField("addressBooks").setDisabled(!checked);
							},
							scope: this
						}
					},
					{
						anchor: '100%',
						disabled: true,
						xtype: "chips",
						submit: false,
						entityStore: go.Stores.get("AddressBook"),
						displayField: "name",
						name: "addressBooks",
						fieldLabel: t("Address books")
					}
				
				]
			}
		];
	},

	
	onLoad : function() {
		this.evalEnableFor();
		
		return go.modules.community.addressbook.CustomFieldSetDialog.superclass.onLoad.call(this);
	},
	
	onBeforeSubmit : function() {
		if(!this.formPanel.values.filter) {
			this.formPanel.values.filter = {};
		}
		
		switch(this.formPanel.getForm().findField('enableFor').getValue()) {
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
		
		if(!enableAbFilter) {
			delete this.formPanel.values.filter.addressBooks;
		} else
		{
			this.formPanel.values.filter.addressBooks = this.formPanel.getForm().findField("addressBooks").getValue().column("id");
		}
		
		return go.modules.community.addressbook.CustomFieldSetDialog.superclass.onBeforeSubmit.call(this);
	},
	
	
	evalEnableFor : function() {
		var enableFor = this.formPanel.getForm().findField('enableFor');
		
		this.formPanel.getForm().findField("enableAddressBookFilter").setValue(!!this.formPanel.entity.filter.addressBooks);
		
		go.Stores.get("AddressBook").get(this.formPanel.entity.filter.addressBooks, function(addressBooks) {
			this.formPanel.getForm().findField("addressBooks").setValue(addressBooks);
		}, this);
		
		switch(this.formPanel.entity.filter.isOrganization) {
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

