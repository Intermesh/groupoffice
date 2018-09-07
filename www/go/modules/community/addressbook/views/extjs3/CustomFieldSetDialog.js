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
					},{
						xtype: "hidden",
						name: "enableCondition",
						value: ""
					}]
			}
		];
	},
	
	onEnableForChange : function(field, radioBtn) {
		var c;
		
		switch(radioBtn.inputValue) {
			case 'contacts':
				c = "isOrganization = false";
				break;
				
			case 'organizations':
				c = "isOrganization = true";
				break;
				
			default:				
				c = "";
				break;
				
		}
				
		this.formPanel.getForm().findField('enableCondition').setValue(c);
	},
	
	onLoad : function() {
		this.evalEnableFor();
		
		go.modules.community.addressbook.CustomFieldSetDialog.superclass.onLoad.call(this);
	},
	
	evalEnableFor : function() {
		var pattern = /isOrganization = (true|false)/, 
						c = this.formPanel.getForm().findField('enableCondition').getValue(),
						enableFor = this.formPanel.getForm().findField('enableFor');
		
		match = pattern.exec(c);
		
		if(!match) {
			enableFor.setValue("all");
			return
		}
		if(match[1] == "true") {
			enableFor.setValue("organizations");
		} else
		{
			enableFor.setValue("contacts");
		}
	}
});

