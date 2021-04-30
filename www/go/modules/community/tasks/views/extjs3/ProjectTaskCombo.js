go.modules.community.tasks.ProjectTaskCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Tasks","tasks"),
	hiddenName: 'task_id', // Should be taskId as soon as time registration v3 is developed further. :-P
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'title',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	allowNew: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'title'],
				entityStore: "Task",
				baseParams: {
					filter: {
						permissionLevel: go.permissionLevels.write
					}
				}
			})
		});

		go.modules.community.tasks.ProjectTaskCombo.superclass.initComponent.call(this);

	},

	setProjectId: function(id) {
		id = id || false;
		if(!id) {
			return;
		}
		this.projectId = id;
		this.store.setFilter('projectId', {projectId: this.projectId}).load();
	}
});