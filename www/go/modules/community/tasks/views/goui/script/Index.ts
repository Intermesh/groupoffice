import {client, modules, moduleSettings, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {UserSettingsPanel} from "./UserSettingsPanel.js";

modules.register({
	package: "community",
	name: "tasks",
	async init() {
		let tasks: Main;
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

			moduleSettings.addPanel(UserSettingsPanel);
		});
	}
});