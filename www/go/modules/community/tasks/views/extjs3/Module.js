go.Modules.register("community", "tasks", {
	mainPanel: "go.modules.community.tasks.MainPanel",
	title: t("Tasks"),
	entities: ["TaskCategory","PortletTasklist","Settings",{
		name: "Tasklist",
		relations: {
			creator: {store: "UserDisplay", fk: "createdBy"},
			groups: {name: 'Groups'}
		}
	}, {
		name: "Task",
		links: [{
			iconCls: "entity ic-check",
			linkWindow: function (entity, entityId) {
				return new go.modules.community.tasks.TaskDialog();
			},

			linkDetail: function () {
				return new go.modules.community.tasks.TaskDetail();
			},

			linkDetailCards: function () {

				const incomplete = new go.modules.community.tasks.TaskLinkDetail({
					title:  t("Incomplete tasks"),
					link: {
						entity: "Task",
						filter: null
					}
				});

				incomplete.store.setFilter('completed',{complete:  false});

				const completed = 	new go.modules.community.tasks.TaskLinkDetail({

					title:  t("Completed tasks"),
					link: {
						entity: "Task",
						filter: null
					}
				});
				completed.store.setFilter('completed',{complete:  true});

				return [
					incomplete,

					completed]
			}
		}],
		relations: {
			creator: {store: "UserDisplay", fk: "createdBy"},
			modifier: {store: "UserDisplay", fk: "modifiedBy"},
			responsible: {store: 'UserDisplay', fk: 'responsibleUserId'},
			tasklist: {store: 'Tasklist', fk: 'tasklistId'},
			categories: {store: "TaskCategory", fk: "categories"},
		},

		/**
		 * Filter definitions
		 *
		 * Will be used by query fields where you can use these like:
		 *
		 * name: Piet,John age: < 40
		 *
		 * Or when adding custom saved filters.
		 */
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: "Query"
			},
			{
				title: t("Commented at"),
				name: 'commentedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified at"),
				name: 'modifiedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified by"),
				name: 'modifiedBy',
				multiple: true,
				type: 'string'
			}, {
				title: t("Created at"),
				name: 'createdat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Created by"),
				name: 'createdby',
				multiple: true,
				type: 'string'
			},
			{
				title: t("Tasklist"),
				name: 'taskListId',
				multiple: false,
				type: "go.modules.community.tasks.TasklistCombo"
			},
			{
				title: t("Progress"),
				name: 'progress',
				multiple: false,
				type: "go.modules.community.tasks.ProgressCombo"
			},
			{
				name: 'title',
				title: t("Title"),
				type: "string",
				multiple: true
			},{
				title: t("Due"),
				name: 'due',
				multiple: false,
				type: 'date'
			},{
				title: t("Start"),
				name: 'start',
				multiple: false,
				type: 'date'
			},{
				title: t("Responsible"),
				name: 'responsibleUserId',
				multiple: false,
				type: 'usercombo'
			}]

	}],
	initModule: function () {
		// go.Alerts.on("beforeshow", function(alerts, alertConfig) {
		// 	const alert = alertConfig.alert;
		// 	if(alert.entity == "Task" && alert.data && alert.data.type == "assigned") {
		//
		//
		// 		//replace panel promise
		// 		alertConfig.panelPromise = alertConfig.panelPromise.then((panelCfg) => {
		// 			return go.Db.store("User").single(alert.data.assignedBy).then((assigner) =>{
		// 				panelCfg.html += ": " + t("You were assigned to this task by {assignedBy}").replace("{assignedBy}", assigner.displayName);
		// 				panelCfg.notificationBody = panelCfg.html;
		// 				return panelCfg;
		// 			});
		//
		// 		});
		// 	}
		// });
	},


	userSettingsPanels: [
		"go.modules.community.tasks.SettingsPanel"
	]
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
	Project : 3,
	Support : 4

}
