import {avatar, btn, column, comp, datasourcestore, DataSourceStore, datecolumn, t, Table} from "@intermesh/goui";
import {jmapds, img, client} from "@intermesh/groupoffice-core";
import {HistoryDetailWindow} from "./HistoryDetailWindow.js";

export class LogEntryGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource: jmapds("LogEntry"),
				sort: [{
					property: "id",
					isAscending: false
				}],
				relations: {
					creator: {
						dataSource: jmapds("Principal"),
						path: "createdBy"
					}
				},
				queryParams: {
					filter: {
						actions: {}
					}
				}
			}),
			[
				column({
					id: "entityId",
					header: t("ID"),
					hidden: true,
					align: "right",
					width: 80,
					resizable: true
				}),
				column({
					id: "description",
					header: t("Name"),
					resizable: true
				}),
				column({
					id: "entity",
					header: t("Entity"),
					resizable: true
				}),
				column({
					id: "creator",
					header: t("User"),
					resizable: true,
					renderer: (v) => {
						return comp({
								cls: "hbox"
							},

							avatar({
								cls: "inline",
								displayName: v.name,
								backgroundImage: v.avatarId ? client.downloadUrl(v.avatarId) : undefined
							}),

							comp({text: v.name, cls: "history-created-by"})
						)
					}
				}),
				column({
					id: "changes",
					header: t("Changes"),
					resizable: true,
					width: 80,
					align: "center",
					renderer: (columnValue, record, td, table, storeIndex, column) => {
						if (columnValue) {
							return btn({
								cls: "history-changes-button",
								icon: "note",
								handler: async () => {
									const win = new HistoryDetailWindow();
									await win.load(record.id);

									win.show();
								}
							})
						}

						return "";
					}
				}),
				column({
					id: "action",
					header: t("Action"),
					resizable: true,
					renderer: (v) => {
						return t(v.charAt(0).toUpperCase() + v.slice(1));
					}
				}),
				datecolumn({
					id: "createdAt",
					header: t("Date"),
					sortable: true,
					resizable: true
				}),
				column({
					id: "remoteIP",
					header: "IP",
					resizable: true
				}),
				column({
					id: "requestId",
					header: t("Request ID"),
					resizable: true,
					width: 200
				})
			]
		);

		this.fitParent = true;
	}
}