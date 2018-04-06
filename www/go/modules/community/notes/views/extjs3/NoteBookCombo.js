go.modules.notes.NoteBookCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Notebook", "notes"),
	hiddenName: 'noteBookId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: go.Stores.get("community", "NoteBook"),
				baseParams: {
					filter: [{
							permissionLevel: GO.permissionLevels.write
					}]
				}
			})
		});
		
		go.modules.notes.NoteBookCombo.superclass.initComponent.call(this);

	}
});
