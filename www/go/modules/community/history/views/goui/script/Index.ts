import {client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {t, translate} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings.js";

export * from "./HistoryDetailPanel.js";

modules.register({
	package: "community",
	name: "history",
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:history"]) {
				return;
			}

			translate.load(GO.lang.community.history, "community", "History");

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

export {HistoryDetailPanel} from "./HistoryDetailPanel.js";