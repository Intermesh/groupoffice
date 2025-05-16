import {
	Window,
	btn,
	column,
	datasourcestore,
	DataSourceStore,
	datecolumn,
	menu,
	t,
	Table, browser
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class KeyGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource: jmapds("Key"),
				sort: [{
					property: "createdAt"
				}],
				relations: {
					user: {
						dataSource: jmapds("Principal"),
						path: "userId"
					}
				}
			}),
			[
				column({
					id: "id",
					header: "ID",
					hidden: true,
					width: 40,
					sortable: true
				}),
				column({
					id: "name",
					header: t("Name"),
					width: 200,
					sortable: true
				}),
				column({
					id: "user",
					header: t("User"),
					width: 200,
					renderer: (columnValue) => {
						return columnValue.name
					}
				}),
				datecolumn({
					id: "createdAt",
					header: t("Created at"),
					width: 160,
					sortable: true
				}),
				column({
					id: "more",
					width: 30,
					sticky: true,
					renderer: (columnValue, record, td, table1, storeIndex) => {
						return btn({
							icon: "more_vert",
							menu: menu({},
								btn({
									icon: "content_copy",
									text: t("Copy token to clipboard"),
									handler: (button, ev) => {
										browser.copyTextToClipboard(record.accessToken, true);
									}
								}),
								btn({
									icon: "search",
									text: t("View access token"),
									handler: (button, ev) => {
										Window.alert(record.accessToken, t("Access token"));
									}
								}),
								btn({
									icon: "delete",
									text: t("Delete"),
									handler: async (button, ev) => {
										await jmapds("Key").confirmDestroy([record.id]);
									}
								})
							)
						})
					}
				})
			]
		);

		this.fitParent = true;
		this.cls = "bg-lowest";
	}
}