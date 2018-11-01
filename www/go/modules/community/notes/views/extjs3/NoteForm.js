go.modules.community.notes.NoteForm = Ext.extend(go.form.Dialog, {
	stateId: 'notes-noteForm',
	title: t("Note"),
	entityStore: "Note",
	width: 600,
	height: 600,
	
	initFormItems: function () {
		
		var formFieldSets = go.CustomFields.getFormFieldSets("Note"),
			 fieldSetAnchor = formFieldSets.length ? '100% 80%' : '100% 100%';
		
		var items = [{
				xtype: 'fieldset',
				anchor: fieldSetAnchor,
				items: [new go.modules.community.notes.NoteBookCombo(),
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					},
					{
						xtype: 'xhtmleditor',
						name: 'content',
						fieldLabel: "",
						hideLabel: true,
						anchor: '0 -90',
						allowBlank: false
					}]
			}
		].concat(formFieldSets);

		return items;
	}
});
