import {entities, JmapDataSource, modules} from "@intermesh/groupoffice-core";
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
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: t("Query")
			},
			{
				title: t("Entity ID"),
				name: 'entityId',
				multiple: true,
				type: 'number'
			}]
	}],
});

export const logEntryDS = new JmapDataSource("LogEntry");
export const moduleDS = new JmapDataSource("Module");

export {HistoryDetailPanel} from "./HistoryDetailPanel.js";