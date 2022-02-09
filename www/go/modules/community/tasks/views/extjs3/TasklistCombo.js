go.modules.community.tasks.TasklistCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Tasklist"),
	hiddenName: 'tasklistId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	role: null, // set to "list" or "board" to filter the tasklist store
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
	},
	initComponent: function() {
		this.supr().initComponent.call(this);

		if(this.initialConfig.role)
			this.store.setFilter('role', {role: this.initialConfig.role});

		if(go.User.tasksSettings) {
			this.value = go.User.tasksSettings.defaultTasklistId;
		}

	}
});

Ext.reg('tasklistcombo', go.modules.community.tasks.TasklistCombo );