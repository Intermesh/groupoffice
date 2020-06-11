go.customfields.type.NotesDialog = Ext.extend(go.customfields.FieldDialog, {
	
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype:'plainfield',
						name: 'typeLabel',
						fieldLabel: t('Type')
					},{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},{
						anchor: '100%',
						grow: true,
						xtype: "textarea",
						name: "options.formNotes",
						fieldLabel: t("Form"),
						hint: t("These notes will display in the form")
					}, {
						anchor: '100%',
						grow: true,
						xtype: "textarea",
						name: "options.detailNotes",
						fieldLabel: t("Detail"),
						hint: t("These notes will display in the detail view")
					}]
			}];

	}
});

