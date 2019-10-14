go.modules.community.notes.NoteBookForm = Ext.extend(go.form.Dialog, {
	stateId: 'notes-noteBookForm',
	title: t('Notebook'),
	entityStore: "NoteBook",
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						required: true
					}]
			}
		];
	}
});
