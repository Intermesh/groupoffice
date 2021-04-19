go.Modules.register("community", "tasks", {
	mainPanel: "go.modules.community.tasks.MainPanel",
	title: t("Tasks"),
	entities: ["TaskCategory","PortletTasklist","Settings",{
		name: "Tasklist",
		relations: {
			creator: {store: "User", fk: "createdBy"},
			groups: {name: 'Groups'}
		}
	},{
		name: "Task",
		links: [{
			iconCls: "entity ic-check",
			linkWindow: function (entity, entityId) {
				return new go.modules.community.tasks.TaskDialog();
			},

			linkDetail: function () {
				return new go.modules.community.tasks.TaskDetail();
			}
		}],
		relations: {
			creator: {store: "User", fk: "createdBy"},
			modifier: {store: "User", fk: "modifiedBy"},
			responsible: {store: 'User', fk: 'responsibleUserId'},
			tasklist: {store: 'Tasklist', fk: 'tasklistId'}

		}
	}],
	initModule: function () {}
});

go.modules.community.tasks.progress = {
	'needs-action' : t('Needs action'),
	'in-progress': t('In progress'),
	'completed': t('Completed'),
	'failed': t('Failed'),
	'cancelled' : t('Cancelled')
};
