import {
	column,
	datecolumn, DefaultEntity,
	Format,
	Notifier, store,
	t,
	Table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {MailboxDialog} from "./MailboxDialog";

export class MailboxTable extends Table {

	public entity : DefaultEntity|undefined;
	constructor() {
		const mbstore = store({data: []});

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
				header: t("Name"),
				id: "name",
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
					v *= 1024;
					return Format.fileSize(v);
				}
			}),
			column({
				header: t("Usage"),
				id: "usage",
				sortable: false,
				width: 120,
				renderer: (v: number) => {
					v *= 1024;
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
					return v.displayName;
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
					return v.displayName;
				}
			}),

		];

		super(mbstore, columns );
		this.fitParent = true;
		this.rowSelectionConfig =  {
			multiSelect: true
		};
	}
}