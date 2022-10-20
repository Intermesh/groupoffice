(function() {
	const cfg = {
		fieldLabel: t("Task list"),
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
			entityStore: "TaskList",
			filters: {
				permissionLevel: {
					permissionLevel: go.permissionLevels.write
				}
			}
		}
	};

	go.modules.community.tasks.TasklistCombo = Ext.extend(go.form.ComboBox, Ext.apply(cfg,
		{
			initComponent: function () {
				this.supr().initComponent.call(this);

				if (this.initialConfig.role) {
					this.store.setFilter('role', {role: this.initialConfig.role});
				}
				if (go.User.tasksSettings && !("value" in this.initialConfig)) {
					this.value = go.User.tasksSettings.defaultTasklistId;
				}
			}
	}));

	go.modules.community.tasks.TasklistComboBoxReset = Ext.extend(go.form.ComboBoxReset, Ext.apply(cfg, {
		initComponent: function() {
			this.supr().initComponent.call(this);

			if(this.initialConfig.role) {
				this.store.setFilter('role', {role: this.initialConfig.role});
			}

			if(go.User.tasksSettings) {
				this.value = go.User.tasksSettings.defaultTasklistId;
			}

		}
	}));

	Ext.reg('tasklistcombo', go.modules.community.tasks.TasklistCombo );
	Ext.reg('tasklistcomboreset', go.modules.community.tasks.TasklistComboBoxReset );

})();