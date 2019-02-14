go.customfields.FieldSetDialog = Ext.extend(go.form.Dialog, {
	stateId: 'custom-field-set-dialog',
	title: t('Field set'),
	entityStore: "FieldSet",
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [{
						xtype: "hidden",
						name: "entity"
					},
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},{
						xtype: "textarea",
						name: "description",
						fieldLabel: t("Description"),
						anchor: "100%",
						grow: true,
						hint: t("This description will show in the edit form")
					}			
				]
			}
		];
	}
});
