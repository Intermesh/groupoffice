import {btn, column, datasourcestore, DataSourceStore, datecolumn, Format, hr, menu, t, Table} from "@intermesh/goui";
import {jmapds, principalDS} from "@intermesh/groupoffice-core";
import {MailboxDialog} from "./MailboxDialog.js";

export class MailboxTable extends Table<DataSourceStore> {
	constructor() {
		const store = datasourcestore({
			dataSource: jmapds("MailBox"),
			sort: [{property: "username", isAscending: true}],
			relations: {
				creator: {path: "createdBy", dataSource: principalDS},
				modifier: {path: "createdBy", dataSource: principalDS}
			}
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
				sortable: true
			}),
			column({
				header: t("Created by"),
				id: "creator/name",
				hidden: true,
				sortable: true,
				resizable: true
			}),
			datecolumn({
				header: t("Modified at"),
				id: "modifiedAt",
				hidden: true,
				sortable: true
			}),
			column({
				header: t("Modified by"),
				id: "modifier/name",
				hidden: true,
				resizable: true
			}),

			column({
				width: 48,
				id: "btn",
				sticky: true,
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
		this.rowSelectionConfig =  {
			multiSelect: true
		};
		this.scrollLoad = true;

		this.stateId = "maildomains-mailbox-table";

	}
}