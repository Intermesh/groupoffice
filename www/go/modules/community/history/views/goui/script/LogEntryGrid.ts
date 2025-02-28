import {btn, column, comp, datasourcestore, DataSourceStore, datecolumn, t, Table} from "@intermesh/goui";
import {jmapds, img} from "@intermesh/groupoffice-core";

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
					header: t("Entity"),
					id: "entity",
					resizable: true
				}),
				column({
					header: t("User"),
					id: "creator",
					resizable: true,
					renderer: (v) => {
						return comp({
								cls: "hbox"
							},
							img({
								cls: "goui-avatar",
								blobId: v.avatarId
							}),
							comp({text: v.name, cls:"history-created-by"})
						)
					}
				}),
				column({
					id: "changes",
					header: t("Changes"),
					resizable: true,
					width: 80,
					align: "center",
					renderer: (v) => {
						if (v) {
							return btn({
								cls: "history-changes-button",
								icon: "note",
								handler: () => {

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