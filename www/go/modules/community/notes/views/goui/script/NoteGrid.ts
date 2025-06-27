import {column, datasourcestore, DataSourceStore, datetimecolumn, menucolumn, t, table, Table} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {NoteDialog} from "./NoteDialog";

export class NoteGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource: jmapds("Note"),
				sort: [{
					property: "name"
				}]
			}),
			[
				column({
					header: t("Name"),
					id: "name",
					sortable: true
				}),
				datetimecolumn({
					header: t("Created At"),
					id: "createdAt",
					sortable: true
				})
			]
		);

		this.on("rowdblclick", async ({target, storeIndex}) => {
			const dlg = new NoteDialog();
			dlg.show();
			await dlg.load(target.store.get(storeIndex)!.id);
		});

		this.fitParent = true;
	}
}