go.modules.notes.NoteBookForm = Ext.extend(go.form.FormWindow, {
	stateId: 'notes-noteBookForm',
	title: t('Notebook', 'notes'),
	entityStore: go.stores.NoteBook,
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
