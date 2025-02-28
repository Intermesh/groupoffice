import {client, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";

const tester = false;

if (tester) {
	modules.register({
		package: "community",
		name: "history",
		async init() {
			client.on("authenticated", (client, session) => {
				if (!session.capabilities["go:community:history"]) {
					return;
				}

				router.add(/^history\/(\d+)$/, () => {
					modules.openMainPanel("history");
				});

				modules.addMainPanel("community", "history", "history", "History", () => {
					return new Main();
				});
			});
		}
	});
}