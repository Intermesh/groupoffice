import {modules} from "@intermesh/groupoffice-core";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {t} from "@intermesh/goui";

modules.register({
	name: "tasks",
	package: "community",
	userSettingsPanels: [UserSettingsPanel],
	mainPanel: "go.modules.community.tasks.MainPanel",
		title: t("Tasks"),
		entities: ["TaskListGrouping", "TaskCategory",{
		name: "TaskList",
		relations: {
			group: {store: "TaskListGrouping", fk: "groupingId"},
			creator: {store: "Principal", fk: "createdBy"}
		}
	}, {
		name: "Task",
		links: [{
			iconCls: "entity ic-assignment",
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
		filters: {
	text: {
		wildcards: false,
		type: "string",
		multiple: false,
		title: t("Query")
	},
	commentedat: {
		title: t("Commented at"),
		multiple: false,
		type: 'date'
	},
	modifiedat: {
		title: t("Modified at"),
		multiple: false,
		type: 'date'
	},
	modifiedBy: {
		title: t("Modified by"),
		multiple: true,
		type: 'go.users.UserCombo',
		typeConfig: {value: null}
	},
	createdat: {
		title: t("Created at"),
		multiple: false,
		type: 'date'
	},
	createdby: {
		title: t("Created by"),
		multiple: true,
		type: 'go.users.UserCombo',
		typeConfig: {value: null}
	},
	tasklistid: {
		title: t("List"),
		multiple: false,
		type: "go.modules.community.tasks.TasklistCombo"
	},
	progress: {
		title: t("Progress"),
		multiple: false,
		type: "go.modules.community.tasks.ProgressCombo"
	},
	title: {
		title: t("Title"),
		type: "string",
		multiple: true
	},
	due: {
		title: t("Due"),
		multiple: false,
		type: 'date'
	},
	start: {
		title: t("Start"),
		multiple: false,
		type: 'date'
	},
	responsibleUserId: {
		title: t("Responsible"),
		multiple: false,
		type: 'go.users.UserCombo',
		typeConfig: {value: null}
	}
}


	}]


	// userSettingsPanels: [
	// 	"go.modules.community.tasks.SettingsPanel"
	// ]
});