import {
	btn,
	column,
	comp,
	DataSourceStore,
	datasourcestore,
	datecolumn,
	datetimecolumn, DefaultEntity,
	Format,
	t,
	Table,
	Window
} from "@intermesh/goui";
import {JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {DomainDialog} from "./DomainDialog.js";
import {mailDomainStatus} from "./MailDomain.js";

export class DomainTable extends Table<DataSourceStore> {
	constructor() {

		const store = datasourcestore({
			dataSource: jmapds("MailDomain"),
			sort: [{property: "domain", isAscending: true}],
			relations: {
				owner: {
					path: "userId",
					dataSource: jmapds("Principal")
				},
				creator: {
					path: "createdBy",
					dataSource: jmapds("Principal")
				},
				modifier: {
					path: "modifiedBy",
					dataSource: jmapds("Principal")
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
			}),
			column({
				header: t("Owner"),
				id: "owner",
				resizable: true,
				sortable: true,
				width: 200,
				renderer: (v, _record) => {
					return v.name;
				},
				hidden: true
			}),
			column({
				header: t("Description"),
				id: "description",
				resizable: true,
				sortable: true,
				hidden: true
			}),
			// column({
			// 	header: t("Aliases"),
			// 	id: "maxAliases",
			// 	sortable: false,
			// 	width: 120,
			// 	renderer: (v, record) => {
			// 		return record.aliases.length + "/" + (v > 0 ? v : t("Unlimited"));
			// 	}
			// }),
			// column({
			// 	header: t("Mailboxes"),
			// 	id: "maxMailboxes",
			// 	sortable: false,
			// 	width: 120,
			// 	renderer: (v, record) => {
			// 		return record.mailboxes.length + "/" + (v > 0 ? v : t("Unlimited"));
			// 	}
			// }),
			column({
				header: t("Quota"),
				id: "totalQuota",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v) => {
					if(v === 0) {
						return t("Unlimited");
					}
					return Format.fileSize(v);
				},
				hidden: true
			}),
			column({
				header: t("Used quota"),
				id: "sumUsedQuota",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					return (v > 0) ? Format.fileSize(v) : "0B";
				},
				hidden: true

			}),
			column({
				header: t("Usage"),
				id: "sumUsage",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
						return (v > 0) ? Format.fileSize(v) : "0B";
				},
				hidden: true

			}),
			column({
				header: t("Active"),
				id: "active",
				resizable: false,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					return v ? t("Yes"): t("No");
				},
				hidden: true

			}),
			column({
				header: t("Backup MX"),
				id: "backupMx",
				resizable: false,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					return v ? t("Yes"): t("No");
				},
				hidden: true

			}),
			datecolumn({
				header: t("Created at"),
				id: "createdAt",
				width: 150,
				sortable: true,
				hidden: true
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
				sortable: true,
				hidden: true
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
				header: t("Status"),
				id: "mxStatus",
				resizable: false,
				width: 80,
				renderer: (v, record) => {

					return mailDomainStatus(record);


				}
			})
		];

		super(store, columns );
		this.fitParent = true;
		this.rowSelectionConfig =  {
			multiSelect: true
		};
		this.on("rowdblclick", async ( {storeIndex}) => {
			const dlg = new DomainDialog();
			dlg.show();
			await dlg.load(this.store.get(storeIndex)!.id);
		});
	}
}