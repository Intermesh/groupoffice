import {client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import { t } from "@intermesh/goui";
import {SettingsPanel} from "./SettingsPanel.js";


modules.register({
	package: "community",
	name: "tasks",
	async init() {

		let  tasks: Main;
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:tasks"]) {
				return;
			}

			router.add(/^tasks\/(\d+)$/, (taskId) => {
				modules.openMainPanel("tasks");
				tasks.showTask(taskId);
			});

			modules.addMainPanel("community", "tasks", "tasks", "Tasks", () => {
				tasks = new Main();
				return tasks;
			});

			modules.addAccountSettingsPanel("community", "tasks", "tasks", t("Tasks"), "check", () => {
				return new SettingsPanel();
			});
		});
	}
});

export const taskDS = new JmapDataSource("Task");
export const tasklistDS = new JmapDataSource("TaskList");
export const tasklistGroupingDS = new JmapDataSource("TaskListGrouping");
export const taskCategoryDS = new JmapDataSource("TaskCategory");
export const commentDS = new JmapDataSource("Comment");
export const projectDS = new JmapDataSource("Project3");