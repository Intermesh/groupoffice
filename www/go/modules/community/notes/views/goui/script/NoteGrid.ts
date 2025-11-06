import {column, datasourcestore, DataSourceStore, datetimecolumn, t, Table} from "@intermesh/goui";
import {NoteDialog} from "./NoteDialog";
import {noteDS} from "./Index.js";
import {customFields} from "@intermesh/groupoffice-core";

export class NoteGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				dataSource:noteDS,
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
			].concat(...customFields.getTableColumns("Note"))
		);

		this.on("rowdblclick", async ({target, storeIndex}) => {
			const dlg = new NoteDialog();
			dlg.show();
			await dlg.load(target.store.get(storeIndex)!.id);
		});

		this.on("delete", async ({target}) => {
			const ids = this.rowSelection!.getSelected()!.map(row => row.record.id);

			await noteDS.confirmDestroy(ids);
		});

		this.fitParent = true;
	}
}