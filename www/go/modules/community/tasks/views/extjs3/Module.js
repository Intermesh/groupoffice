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
			},

			linkDetailCards : function() {
				return [new go.modules.community.tasks.TaskLinkDetail({
					link: {
						title: t("Tasks"),
						iconCls: 'icon ic-check',
						entity: "Task",
						filter: null
					}
				})]
			}
		}],
		relations: {
			creator: {store: "User", fk: "createdBy"},
			modifier: {store: "User", fk: "modifiedBy"},
			responsible: {store: 'User', fk: 'responsibleUserId'},
			tasklist: {store: 'Tasklist', fk: 'tasklistId'}

		}
	}],
	initModule: function () {
		go.Alerts.on("beforeshow", function(alerts, alertConfig) {
			const alert = alertConfig.alert;
			if(alert.entity == "Task" && alert.data && alert.data.type == "assigned") {


				//replace panel promise
				alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
					return go.Db.store("User").single(alert.data.assignedBy).then((assigner) =>{
						panelCfg.html += ": " + t("You were assigned to this task by {assignedBy}").replace("{assignedBy}", assigner.displayName);
						panelCfg.notificationBody = panelCfg.html;
						return panelCfg;
					});

				});
			}
		});
	}
});

go.modules.community.tasks.progress = {
	'needs-action' : t('Needs action'),
	'in-progress': t('In progress'),
	'completed': t('Completed'),
	'failed': t('Failed'),
	'cancelled' : t('Cancelled')
};

go.modules.community.tasks.listTypes = {
	List : 1,
	Board : 2,
	Project : 3
}
