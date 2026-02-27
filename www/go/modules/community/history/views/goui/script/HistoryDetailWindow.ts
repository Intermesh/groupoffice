import {column, comp, Component, EntityID, Format, store, t, table, Window} from "@intermesh/goui";
import {logEntryDS} from "./Index";

export class HistoryDetailWindow extends Window {
	private dateComp: Component;
	private changesComp: Component;

	constructor() {
		super();

		this.title = t("History");
		this.width = 800;
		this.height = 600;

		this.collapsible = true;
		this.stateId = "history-detail";
		this.resizable = true;

		this.items.add(
			comp({cls: "pad scroll fit", flex: 1},
				this.dateComp = comp(),
				comp({tagName: "h4", text: t("Changes")}),
				this.changesComp = comp()
			)
		);
	}

	private formatValue(v:any): string {

		if(v === null || v === undefined) {
			return '<i>null</i>';
		}
		switch (typeof v) {
			case "string":
				if(v.match(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}/)) {
					return Format.dateTime(v);
				} else {
					return v.htmlEncode();
				}

			case "object":
				let str = "";
				for(let key in v) {
					str += `${key}: ${this.formatValue(v[key])} \n`;
				}
				return str;

			default:
				return JSON.stringify(v);

		}
	}

	public async load(logEntryId: EntityID) {
		const logEntry = await logEntryDS.single(logEntryId);

		if (!logEntry)
			return

		this.title = logEntry.description;

		this.dateComp.text = t("Datum") + ": " + Format.dateTime(logEntry.createdAt);

		const changesJson = JSON.parse(logEntry.changes);

		const changes: [string, string][] = Object.entries(changesJson);

		switch (logEntry.action) {
			case "create":
				for (let i = 0; i < changes.length; i++) {
					const [key, value] = changes[i];

					this.changesComp.items.add(
						comp({
							html: `<b>${key}: </b> ${this.formatValue(value)}`
						})
					);
				}

				break;
			case "update":
				const tableData = [];

				for (let i = 0; i < changes.length; i++) {
					const change: [string, string] = changes[i];

					tableData.push({
						name: change[0],
						old: this.formatValue(change[1][0]),
						new: this.formatValue(change[1][1])
					});
				}

				this.changesComp.items.add(
					table({
						cls: "history-changes-table",
						columns: [
							column({
								id: "name",
								header: t("Name")
							}),
							column({
								id: "old",
								header: t("Old")
							}),
							column({
								id: "new",
								header: t("New")
							})
						],
						store: store({data: tableData}),
						fitParent: true,
						rowSelectionConfig: {
							multiSelect: false
						}
					})
				);

				break;
		}
	}
}