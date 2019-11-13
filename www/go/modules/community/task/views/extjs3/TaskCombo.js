go.modules.community.task.TaskCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Tasklist","tasks"),
	hiddenName: 'taskId',
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
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "Tasklist",
		baseParams: {
			filter: {
					permissionLevel: go.permissionLevels.write
			}
		}
	}
});