import {column, DataSourceStore, datasourcestore, datecolumn, datetimecolumn, Format, t, Table} from "@intermesh/goui";
import {JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {DomainDialog} from "./DomainDialog";

export class DomainTable extends Table<DataSourceStore> {
	constructor() {

		const store = datasourcestore({
			dataSource: jmapds("MailDomain"),
			sort: [{property: "domain", isAscending: true}],
			relations: {
				owner: {
					path: "userId",
					dataSource: jmapds("UserDisplay")
				},
				creator: {
					path: "createdBy",
					dataSource: jmapds("UserDisplay")
				},
				modifier: {
					path: "modifiedBy",
					dataSource: jmapds("UserDisplay")
				},
				mailaccounts: {
					dataSource: jmapds("MailBox"),
					path: "mailboxes"
				},
				mailaliases: {
					dataSource: jmapds("MailAlias"),
					path: "aliases"
				}

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
				id: "domain",
				resizable: true,
				header: t("Domain"),
				sortable: true
				// width: 200
			}),
			column({
				header: t("Owner"),
				id: "owner",
				resizable: true,
				sortable: true,
				width: 200,
				renderer: (v, _record) => {
					return v.displayName;
				},
			}),
			column({
				header: t("Description"),
				id: "description",
				resizable: true,
				sortable: true
			}),
			column({
				header: t("Aliases"),
				id: "maxAliases",
				sortable: false,
				width: 120,
				renderer: (v, record) => {
					return record.sumAliases + "/" + v
				}
			}),
			column({
				header: t("Mailboxes"),
				id: "maxMailboxes",
				sortable: false,
				width: 120,
				renderer: (v, record) => {
					return record.sumMailboxes + "/" + v
				}
			}),
			column({
				header: t("Quota"),
				id: "totalQuota",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v) => {
					v *= 1024;
					return Format.fileSize(v);
				}
			}),
			column({
				header: t("Used quota"),
				id: "sumUsedQuota",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					// debugger;
					// let q= 0;
					// for(const mb of record.mailboxes) {
					// 	q += mb.quota;
					// }
					// q *= 1024;
					v *= 1024;
					return Format.fileSize(v);
				}

			}),
			column({
				header: t("Usage"),
				id: "usage",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (_v, record) => {
					let q = 0;
					for(const mb of record.mailboxes) {
						q += mb.usage;
					}
					return Format.fileSize(q);
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
			column({
				header: t("Backup MX"),
				id: "backupMx",
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
					return v.displayName;
				}
			}),
			datecolumn({
				header: t("Modified at"),
				id: "modifiedAt",
				width: 150,
				sortable: true
			}),
			column({
				header: t("Modified by"),
				id: "modifier",
				hidden: true,
				resizable: true,
				renderer: (v) => {
					return v.displayName;
				}
			}),

		];

		super(store, columns );
		this.fitParent = true;
		this.rowSelectionConfig =  {
			multiSelect: true
		};
		this.on("rowdblclick", async (table, rowIndex, ev) => {
			const dlg = new DomainDialog();
			dlg.show();
			await dlg.load(table.store.get(rowIndex)!.id);
		});

	}
}