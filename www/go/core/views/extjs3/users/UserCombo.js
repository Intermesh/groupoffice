go.users.UserCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("User"),
	hiddenName: 'userId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'displayName',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	store: new go.data.Store({
		fields: ['id', 'displayName'],
		entityStore: "User"
	})
});


Ext.reg("usercombo", go.users.UserCombo);
