go.Modules.register("community", "task", {
	mainPanel: "go.modules.community.task.MainPanel",
	title: t("Tasks"),
	entities: ["TasksCategory","TasksPortletTasklist","TasksSettings",{
		name: "TasksTask",
		links: [{

			iconCls: "entity ic-check",

			linkWindow: function (entity, entityId) {
				return new go.modules.community.task.TaskDialog();
			},

			linkDetail: function () {
				return new go.modules.community.task.TaskDetail();
			}
		}],
		relations: {
			// creator: {store: "Alert", fk: "taskId"},
			// modifier: {store: "Alert", fk: "taskId"},
			creator: {store: "User", fk: "createdBy"},
			modifier: {store: "User", fk: "modifiedBy"}
		},
	},
	{
		name: "TasksTasklist",
		relations: {
			creator: {store: "User", fk: "createdBy"}
		}
	}

],
	initModule: function () {}
});
