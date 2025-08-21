go.Modules.register("community", "tasks", {
	mainPanel: "go.modules.community.tasks.MainPanel",
	title: t("Tasks"),
	entities: ["TaskListGrouping", "TaskCategory","PortletTasklist","Settings",{
		name: "TaskList",
		relations: {
			group: {store: "TaskListGrouping", fk: "groupingId"},
			creator: {store: "Principal", fk: "createdBy"},

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
			creator: {store: "Principal", fk: "createdBy"},
			modifier: {store: "Principal", fk: "modifiedBy"},
			responsible: {store: 'Principal', fk: 'responsibleUserId'},
			tasklist: {store: 'TaskList', fk: 'tasklistId'},
			categories: {store: "TaskCategory", fk: "categories"},
			project: {store: "Project3", fk: "projectId"},
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
				title: t("Query")
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
				type: 'go.users.UserCombo',
				typeConfig: {value: null}
			}, {
				title: t("Created at"),
				name: 'createdat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Created by"),
				name: 'createdby',
				multiple: true,
				type: 'go.users.UserCombo',
				typeConfig: {value: null}
			},
			{
				title: t("List"),
				name: 'tasklistid',
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
				type: 'go.users.UserCombo',
				typeConfig: {value: null}
			}]

	}],
	initModule: function () {
		go.Alerts.on("beforeshow", function(alerts, alertConfig) {
			const alert = alertConfig.alert;
			if(alert.entity == "Task" || alert.entity == "SupportTicket") {

				switch(alert.tag) {
					case "assigned":
						//replace panel promise
						alertConfig.panelPromise = alertConfig.panelPromise.then(async (panelCfg) => {
							let assigner;
							try {
								assigner = await go.Db.store("Principal").single(alert.data.assignedBy);
							} catch (e) {

							}

							if(!assigner) {
								assigner = {name: t("Unknown user")};
							}

							const msg = go.util.Format.dateTime(alert.triggerAt) + ": " +t("You were assigned to this task by {assigner}").replace("{assigner}", assigner.name);
							panelCfg.items = [{html: msg }];
							panelCfg.notificationBody = msg;
							return panelCfg;
						});
						break;

					case "createdforyou":
//replace panel promise
						alertConfig.panelPromise = alertConfig.panelPromise.then(async (panelCfg) => {

							let creator;
							try {
								creator = await go.Db.store("Principal").single(alert.data.createdBy);
							} catch (e) {

							}

							if(!creator) {
								creator = {name: t("Unknown user")};
							}

							const msg = go.util.Format.dateTime(alert.triggerAt) + ": " +t("A new task was created in your list by {creator}").replace("{creator}", creator.name);
							panelCfg.items = [{html: msg}];
							panelCfg.notificationBody = msg
							return panelCfg;

						});
						break;
				}

			}
		});


		async function showBadge() {
			const count = await go.Jmap.request({method: "Task/countMine"});

			GO.mainLayout.setNotification('tasks', count,'orange');
		}

		GO.mainLayout.on("authenticated", () => {
			if(go.Modules.isAvailable("community", "tasks")) {

				go.Db.store("Task").on("changes", () => {
					showBadge();
				});

				showBadge();
			}
		})

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
	List : "list",
	Board : "board",
	Project : "project",
	Support : "support"

}
