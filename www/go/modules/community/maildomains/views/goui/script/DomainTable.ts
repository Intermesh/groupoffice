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
					return record.aliases.length + "/" + (v > 0 ? v : t("Unlimited"));
				}
			}),
			column({
				header: t("Mailboxes"),
				id: "maxMailboxes",
				sortable: false,
				width: 120,
				renderer: (v, record) => {
					return record.mailboxes.length + "/" + (v > 0 ? v : t("Unlimited"));
				}
			}),
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
					v = parseInt(v);
					v *= 1024;
					return (v > 0) ? Format.fileSize(v) : "0B";
				}

			}),
			column({
				header: t("Usage"),
				id: "sumUsage",
				resizable: true,
				width: 100,
				sortable: false,
				renderer: (v, _record) => {
					v = parseInt(v);
					v *= 1024;
					return (v > 0) ? Format.fileSize(v) : "0B";
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
			column({
				header: t("Status"),
				id: "mxStatus",
				resizable: false,
				width: 80,
				renderer: (v, record) => {
					let allIsWell = record.mxStatus && record.dmarcStatus && record.spfStatus;

					for (const selector in record.dkim) {
						allIsWell = allIsWell && record.dkim[selector].status;
					}
					let iconCls = "icon";
					if (!allIsWell) {
						iconCls += " accent";
					}
					return btn({icon: (allIsWell ? "check_circle" : "warning"), cls: iconCls,
						handler:(_btn, _ev) => { this.onStatusBtnClick(allIsWell, record)}});

				}
			})
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

	private onStatusBtnClick(allIsWell: boolean, record: DefaultEntity) {
		let msg = "";
		if(allIsWell) {
			msg += t("No DNS problems found!", "community", "maildomains");
		} else {
			if (Object.keys(record.dkim).length === 0) {
				msg += "&bull;&nbsp;" + t("No DKIM records found.") + "<br/>";
			} else {
				for (const selector in record.dkim) {
					if (!record.dkim[selector].status) {
						msg += "&bull;$nbsp;" + t("Missing or invalid DKIM record for ", "community", "maildomains") +
							" " + selector + ".<br/>";
					}
				}
			}
			if (!record.spfStatus) {
				msg += "&bull;&nbsp;" + t("Missing or invalid SPF TXT record", "community","maildomains") + ".<br>";
			}
			if (!record.dmarcStatus) {
				msg += "&bull;&nbsp;" + t("Missing or invalid DMARC TXT record", "community","maildomains") + ".<br>";
			}
			if (!record.mxStatus) {
				msg += "&bull;&nbsp;" + t("Missing or invalid MX TXT record", "community","maildomains") + ".<br>";
			}
		}
		void Window.alert(msg, t("DNS Status"));
	}
}