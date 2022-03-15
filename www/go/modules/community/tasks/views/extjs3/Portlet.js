
go.modules.community.tasks.Portlet = Ext.extend(go.modules.community.tasks.TaskGrid, {

	stateId: 'tasks-portlet',


	autoHeight: true,
	maxHeight: dp(600),



	afterRender: function () {
		this.supr().afterRender.call(this);
		const lists = go.User.taskPortletTaskLists.length ? go.User.taskPortletTaskLists : [go.User.tasksSettings.defaultTasklistId];
		this.store.setFilter('tasklistIds', {tasklistId: lists});
		this.store.setFilter('incomplete', {complete: false, start: "<=now"});
		this.store.load();

		this.on("rowclick", function (grid, rowClicked, e) {
			go.Router.goto('task/' + grid.selModel.selections.keys[0]);
		});
	}
});

go.modules.community.tasks.PortletSettingsDialog = Ext.extend(go.form.Dialog, {
	title: t("Visible tasklists"),
	entityStore: "User",
	width: dp(500),
	height: dp(500),
	modal: true,
	collapsible: false,
	maximizable: false,
	layout: 'fit',
	showCustomfields: false,

	initFormItems: function() {
		return [
			new Ext.form.FieldSet({
				xtype: 'fieldset',
				items: [
					{
						layout: "form",
						items: [
							new go.form.multiselect.Field({
								valueIsId: true,
								idField: 'taskListId',
								displayField: 'name',
								entityStore: 'TaskList',
								name: 'taskPortletTaskLists',
								hideLabel: true,
								emptyText: t("Please select..."),
								pageSize: 50,
								fields: ['id', 'name']

							})
						]
					}
				]
			})
		];
	}
});


GO.mainLayout.onReady(function () {
	if (go.Modules.isAvailable("legacy", "summary") && go.Modules.isAvailable("community", "tasks"))
	{
		var tasksGrid = new go.modules.community.tasks.Portlet();

		GO.summary.portlets['portlet-tasks'] = new GO.summary.Portlet({
			id: 'portlet-tasks',
			//iconCls: 'go-module-icon-tasks',
			title: t("Tasks", "tasks"),
			layout: 'fit',
			tools: [{
					id: 'gear',
					handler: function () {
						const dlg = new go.modules.community.tasks.PortletSettingsDialog({
							listeners: {
								hide: function () {
									setTimeout(function() {
										tasksGrid.store.setFilter('tasklistIds', {tasklistId: go.User.taskPortletTaskLists})
										tasksGrid.store.reload();
									})
								},
								scope: this
							}
						});
						dlg.load(go.User.id).show();
					}
				}, {
					id: 'close',
					handler: function (e, target, panel) {
						panel.removePortlet();
					}
				}],
			items: tasksGrid,
			autoHeight: true
		});
	}
});
