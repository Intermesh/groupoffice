import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {t} from "@intermesh/goui";

export * from "./HistoryDetailPanel.js";

modules.register({
	package: "community",
	name: "history",
	panels: [
		{
			title: t("History"),
			cmp: Main,
			id: "history",
			routes: {
				"^history$"() {
					this.show();
				}
			}
		}
	],
	// systemSettingsPanels: [Settings], TODO this aint right??
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