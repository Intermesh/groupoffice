go.modules.community.notes.NoteBookCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Notebook"),
	hiddenName: 'noteBookId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: false,
	forceSelection: true,
	allowBlank: false,
	store: {
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "NoteBook",
		baseParams: {
			filter: {
					permissionLevel: go.permissionLevels.write
			}
		}
	}
});
Ext.reg('notebookcombo', go.modules.community.notes.NoteBookCombo );
