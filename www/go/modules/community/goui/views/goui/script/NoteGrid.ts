import {NoteDialog} from "./NoteDialog.js";
import {column, datasourcestore, datetimecolumn, JmapDataSource, t, Table, DataSourceStore} from "@intermesh/goui";

export class NoteGrid extends Table<DataSourceStore> {

	constructor() {

		super(
			datasourcestore({
				dataSource: JmapDataSource.store("Note"),
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

		this.on("rowdblclick", async (table, rowIndex, ev) => {
			const dlg = new NoteDialog();
			dlg.show();
			await dlg.load(table.store.get(rowIndex).id);
		});
	}

}