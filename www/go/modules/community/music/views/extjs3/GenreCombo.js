go.modules.community.music.GenreCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Genre"),
	hiddenName: 'genreId',
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
	store: new go.data.Store({
		fields: ['id', 'name'],
		entityStore: "Genre"
	})
});

// Register an xtype so we can use the component easily.
Ext.reg("genrecombo", go.modules.community.music.GenreCombo);
