import {
	btn,
	column, DataSourceStore, datasourcestore,
	datecolumn, DefaultEntity, hr, menu,
	Notifier, store,
	t,
	Table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {MailboxDialog} from "./MailboxDialog";
import {AliasDialog} from "./AliasDialog";

export class AliasTable extends Table<DataSourceStore> {

	constructor() {
		const store = datasourcestore({
			dataSource: jmapds("MailAlias"),
			queryParams: {
				limit: 50,
				filter: {
				}
			},
			sort: [{property: "address", isAscending: true}]
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
				id: "address",
				resizable: true,
				header: t("Address"),
				sortable: true,
				renderer: (v, _record) => {
					if(v.charAt(0) === "@") {
						v = "*"+v;
					}
					return v;
				}
			}),
			column({
				id: "goto",
				resizable: true,
				header: t("Goto"),
				sortable: true
			}),

			datecolumn({
				header: t("Created at"),
				id: "createdAt",
				width: 120
			}),
			datecolumn({
				header: t("Modified at"),
				id: "modifiedAt",
				width: 120
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
									const d = new AliasDialog();
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
									jmapds("MailAlias").confirmDestroy([book.id]);
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