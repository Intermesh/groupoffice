import {column, comp, Component, DateTime, EntityID, Format, store, t, table, Window} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

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
			comp({cls: "pad scroll"},
				this.dateComp = comp({}),
				comp({tagName: "h4", text: t("Changes")}),
				this.changesComp = comp({})
			)
		)
	}

	public async load(logEntryId: EntityID) {
		const logEntry = await jmapds("LogEntry").single(logEntryId);

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
							html: `<b>${key}: </b> ${value!.toString()}`
						})
					)
				}

				break;
			case "update":
				const tableData = [];

				for (let i = 0; i < changes.length; i++) {
					let change: [string, string] = changes[i];

					let oldStr = change[1][0];
					let newStr = change[1][1];

					if (typeof oldStr === "object") {
						oldStr = Object.entries(oldStr).map(([key], value) => {
							return `${key}: ${value} \n`;
						}).join("");
					}

					if (typeof newStr === "object") {
						newStr = Object.entries(newStr).map(([key], value) => {
							return `${key}: ${value} \n`;
						}).join("");
					}

					newStr = !isNaN(new Date(newStr).getTime()) ?  Format.dateTime(newStr) : newStr;
					oldStr = !isNaN(new Date(oldStr).getTime()) ?  Format.dateTime(oldStr) : oldStr;

					tableData.push({
						name: change[0],
						old: oldStr,
						new: newStr
					});
				}

				this.changesComp.items.add(
					table({
						cls: "history-changes-table scroll",
						columns: [
							column({
								id: "name",
								header: t("Name")
							}),
							column({
								id: "old",
								header: t("Old"),

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
						},
						flex: 1
					})
				)

				break;
		}
	}
}