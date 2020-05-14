go.Modules.register("community", "tasks", {
	mainPanel: "go.modules.community.tasks.MainPanel",
	title: t("Tasks"),
	entities: ["TaskCategory","PortletTasklist","Settings",{
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
			modifier: {store: "User", fk: "modifiedBy"}
		},
	},
	{
		name: "Tasklist",
		relations: {
			creator: {store: "User", fk: "createdBy"}
		}
	}],
	initModule: function () {}
});
