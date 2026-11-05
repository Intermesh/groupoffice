import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {SettingsPanel} from "./SettingsPanel";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {t} from "@intermesh/goui";
import {Main} from "./Main";
import {TaskDialog} from "./TaskDialog";
import {TaskDetail} from "./TaskDetail";
import {TasklistCombo} from "./TasklistCombo";
import {ProgressCombo} from "./ProgressCombo";

modules.register({
	name: "tasks",
	package: "community",
	systemSettingsPanels: [SettingsPanel],
	userSettingsPanels: [UserSettingsPanel],
	entities: [
		"TaskListGrouping",
		"TaskCategory",
		"TaskList",
		{
			name: "Task",
			links: [{
				iconCls: "entity ic-assignment",
				linkWindow: function (_entity, _entityId) {
					return new TaskDialog();
				},

				linkDetail: function () {
					return new TaskDetail();
				},

				linkDetailCards: function () {
					/*
					const incomplete = new go.modules.community.tasks.TaskLinkDetail({
						title: t("Incomplete tasks"),
						link: {
							entity: "Task",
							filter: null
						}
					});

					incomplete.store.setFilter('completed', {complete: false});

					const completed = new go.modules.community.tasks.TaskLinkDetail({

						title: t("Completed tasks"),
						link: {
							entity: "Task",
							filter: null
						}
					});
					completed.store.setFilter('completed', {complete: true});

					return [incomplete, completed];
					 */
					// TODO: Refactor TaskLinkDetail
					return [];
				}
			}],
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
					type: TasklistCombo
				},
				progress: {
					title: t("Progress"),
					multiple: false,
					type: ProgressCombo
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
		}],
	panels: {
		tasks: {
			cmp: Main,
			title: t("Tasks"),
			routes: {
				"^tasks/(\\d+)$"(taskId: string) {
					this.show();
					this.showTask(taskId);
				}
			}
		}
	}
});

export const taskDS = new JmapDataSource("Task");
export const tasklistDS = new JmapDataSource("TaskList");
export const tasklistGroupingDS = new JmapDataSource("TaskListGrouping");
export const taskCategoryDS = new JmapDataSource("TaskCategory");
export const commentDS = new JmapDataSource("Comment");
export const projectDS = new JmapDataSource("Project3");

export enum ProgressType {
	'needs-action' = 'Needs action',
	'in-progress' = 'In progress',
	'completed' = 'Completed',
	'failed' = 'Failed',
	'cancelled' = 'Cancelled'
}