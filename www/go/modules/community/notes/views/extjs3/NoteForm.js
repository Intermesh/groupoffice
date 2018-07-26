go.modules.community.notes.NoteForm = Ext.extend(go.form.Dialog, {
	stateId: 'notes-noteForm',
	title: t("Note"),
	entityStore: go.Stores.get("Note"),
	width: 600,
	height: 600,
	
	initFormItems: function () {
		var items = [{
				xtype: 'fieldset',
				autoHeight: true,
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
						anchor: '100%',
						height: 300,
						allowBlank: false
					}]
			}
		].concat(go.CustomFields.getFormFieldSets("Note"));

		return items;
	}
});
