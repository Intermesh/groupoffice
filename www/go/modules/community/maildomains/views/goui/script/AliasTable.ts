import {
	column,
	datecolumn,
	Notifier, store,
	t,
	Table
} from "@intermesh/goui";

export class AliasTable extends Table {
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
				id: "address",
				resizable: true,
				header: t("Address"),
				sortable: true
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
		];

		super(mbstore, columns );
		this.fitParent = true;
		this.rowSelectionConfig =  {
			multiSelect: true
		};
		this.on("rowdblclick", async (table, rowIndex, ev) => {
			Notifier.notice("TODO")
		});

	}
}