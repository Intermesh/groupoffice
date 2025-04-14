import {client, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

const tester = true;

if (tester) {
	modules.register({
		package: "community",
		name: "tasks",
		async init() {

			let  tasks: Main;
			client.on("authenticated", (client, session) => {
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
			});
		}
	})
}