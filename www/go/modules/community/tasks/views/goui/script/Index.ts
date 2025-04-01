import {client, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

const tester = true;

if (tester) {
	modules.register({
		package: "community",
		name: "tasks",
		async init() {
			client.on("authenticated", (client, session) => {
				if (!session.capabilities["go:community:tasks"]) {
					return;
				}

				router.add(/^tasks\/(\d+)$/, (taskId) => {
					modules.openMainPanel("tasks");
				});

				modules.addMainPanel("community", "tasks", "tasks", "Tasks", () => {
					return new Main();
				});
			});
		}
	})
}