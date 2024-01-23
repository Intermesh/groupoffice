(function() {
	const cfg = {
		fieldLabel: t("List"),
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
		allowBlank: false
	};

	go.modules.community.tasks.TasklistCombo = Ext.extend(go.form.ComboBox, Ext.apply(cfg,
		{
			initComponent: function () {
				this.supr().initComponent.call(this);

				this.store = new go.data.Store(
					{
						fields: ['id', 'name'],
						entityStore: this.initialConfig.role && this.initialConfig.role == "support" ? "SupportList" : "TaskList",
						filters: {
							permissionLevel: {
								permissionLevel: go.permissionLevels.create
							}
						}
					}
				)

				if (this.initialConfig.role) {
					this.store.setFilter('role', {role: this.initialConfig.role});
				}
				if (go.User.tasksSettings && !("value" in this.initialConfig)) {
					this.value = this.initialConfig.role == "support" ? go.User.supportSettings.defaultTasklistId : go.User.tasksSettings.defaultTasklistId;
				}
			}
	}));

	go.modules.community.tasks.TasklistComboBoxReset = Ext.extend(go.form.ComboBoxReset, Ext.apply(cfg, {
		allowBlank: true,
		initComponent: function() {
			this.supr().initComponent.call(this);

			this.store = new go.data.Store(
				{
					fields: ['id', 'name'],
					entityStore: this.initialConfig.role && this.initialConfig.role == "support" ? "SupportList" : "TaskList",
					filters: {
						permissionLevel: {
							permissionLevel: go.permissionLevels.write
						}
					}
				}
			)

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