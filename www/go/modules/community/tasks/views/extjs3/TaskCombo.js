go.modules.community.tasks.TaskCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Task"),
	hiddenName: 'taskId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'title',
	groupField: "tasklist.name",
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: true,
	store: {
		xtype: "gostore",
		fields: ['id', 'title', {name: "tasklist", type: "relation"}],
		entityStore: "Task",
		baseParams: {
			sort: [{ property: "tasklist", isAscending: true }],
			filter: {
					permissionLevel: go.permissionLevels.write
			}
		}
	}
});