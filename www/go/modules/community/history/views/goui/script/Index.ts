import {client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {t} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings.js";

modules.register({
	package: "community",
	name: "history",
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:history"]) {
				return;
			}

			router.add(/^history\/(\d+)$/, () => {
				modules.openMainPanel("history");
			});

			modules.addMainPanel("community", "history", "history", "History", () => {
				return new Main();
			});

			modules.addSystemSettingsPanel("community", "history", "history", t("History"), "history", () => {
				return new SystemSettings();
			});
		});
	}
});

export const logEntryDS = new JmapDataSource("LogEntry");
export const moduleDS = new JmapDataSource("Module");