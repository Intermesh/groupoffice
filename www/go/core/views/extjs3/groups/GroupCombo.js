go.groups.GroupCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Group"),
	hiddenName: 'groupId',
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
	store: {
		xtype: 'gostore',
		fields: ['id', 'name'],
		entityStore: "Group"
	}
});


Ext.reg("groupcombo", go.groups.GroupCombo);


go.groups.GroupComboReset = Ext.extend(go.form.ComboBoxReset, {
	fieldLabel: t("Group"),
	hiddenName: 'groupId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: true,
	store: {
		xtype: 'gostore',
		fields: ['id', 'name'],
		entityStore: "Group"
	}
});


Ext.reg("groupcomboreset", go.groups.GroupComboReset);