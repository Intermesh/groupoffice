import {
	btn,
	column, datasourcestore, DataSourceStore,
	datecolumn, DefaultEntity,
	Format, hr, menu,
	t,
	Table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {MailboxDialog} from "./MailboxDialog.js";

export class MailboxTable extends Table<DataSourceStore> {
	constructor() {
		const store = datasourcestore({
			dataSource: jmapds("MailBox"),
			queryParams: {
				limit: 50,
				filter: {
				}
			},
			sort: [{property: "username", isAscending: true}]
		});
		const columns = [
			column({
				header: "ID",
				id:"id",
				resizable: true,
				width: 80,
				sortable: true,
				align: "right",
				hidden: true
			}),
			column({
				id: "username",
				resizable: true,
				header: t("Username"),
				sortable: true
			}),
			column({
				header: t("Description"),
				id: "description",
				resizable: true,
				sortable: true,
				width: 200
			}),
			column({
				header: t("Quota"),
				id: "quota",
				resizable: true,
				sortable: true,
				width: 120,
				renderer: (v: number) => {
					return Format.fileSize(v);
				}
			}),
			column({
				header: t("Usage"),
				id: "bytes",
				sortable: false,
				width: 120,
				renderer: (v: number) => {
					return Format.fileSize(v);
				}
			}),
			column({
				header: t("Active"),
				id: "active",
				resizable: false,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					return v ? t("Yes"): t("No");
				}

			}),
			datecolumn({
				header: t("Created at"),
				id: "createdAt",
				hidden: true,
				width: 150,
				sortable: true
			}),
			column({
				header: t("Created by"),
				id: "creator",
				hidden: true,
				sortable: true,
				resizable: true,
				renderer: (v) => {
					return v.name;
				}
			}),
			datecolumn({
				header: t("Modified at"),
				id: "modifiedAt",
				width: 150,
				hidden: true,
				sortable: true
			}),
			column({
				header: t("Modified by"),
				id: "modifier",
				hidden: true,
				resizable: true,
				renderer: (v) => {
					return v.name;
				}
			}),

			column({
				width: 48,
				id: "btn",

				renderer: (columnValue: any, record, td, table, rowIndex) => {


					return btn({
						icon: "more_vert",
						menu: menu({},
							btn({
								icon: "edit",
								text: t("Edit"),
								handler: async (btn) => {
									const book = table.store.get(rowIndex)!;
									const d = new MailboxDialog();
									await d.load(book.id);
									d.show();
								}
							}),
							hr(),
							btn({
								icon: "delete",
								text: t("Delete"),
								handler: async (btn) => {
									const book = table.store.get(rowIndex)!;
									jmapds("MailBox").confirmDestroy([book.id]);
								}
							})

						)
					})
				}
			}),

		];

		super(store, columns );
		this.fitParent = true;
		this.rowSelectionConfig =  {
			multiSelect: true
		};
	}
}