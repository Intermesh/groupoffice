go.modules.core.customfields.FieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'custom-field-set-dialog',
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
					}				
				]
			}
		];
	}
});
