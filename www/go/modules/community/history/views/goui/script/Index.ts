import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {t} from "@intermesh/goui";
import {Settings} from "./Settings.js";

export * from "./HistoryDetailPanel.js";

modules.register({
	package: "community",
	name: "history",
	panels: {
		history: {
			title: t("History"),
			cmp: Main

		}
	},
	systemSettingsPanels: [Settings],
	entities: [{
		name:'LogEntry',
		relations: {
			creator: {store: 'Principal', fk:'createdBy'}
		},
		filters: {
			text: {
				wildcards: false,
				type: "string",
				multiple: false,
				title: t("Query")
			},
			entityId: {
				title: t("Entity ID"),

				multiple: true,
				type: 'number'
			}
		}
	}],
});

export const logEntryDS = new JmapDataSource("LogEntry");
export const moduleDS = new JmapDataSource("Module");

export {HistoryDetailPanel} from "./HistoryDetailPanel.js";