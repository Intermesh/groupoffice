go.modules.community.addressbook.CustomFieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'addressbook-custom-field-set-dialog',
	title: t('Field set'),
	entityStore: go.Stores.get("FieldSet"),
	autoHeight: true,
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
						],
						listeners: {
							scope: this,
							change: this.onEnableForChange
						}
					}]
			}
		];
	},
	
	onEnableForChange : function(field, radioBtn) {
		
		if(!this.formPanel.values.filter) {
			this.formPanel.values.filter = {};
		}
		
		switch(radioBtn.inputValue) {
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
	},
	
	onLoad : function() {
		this.evalEnableFor();
		
		go.modules.community.addressbook.CustomFieldSetDialog.superclass.onLoad.call(this);
	},
	
	evalEnableFor : function() {
		var enableFor = this.formPanel.getForm().findField('enableFor');
		console.log(this.formPanel.entity);
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

