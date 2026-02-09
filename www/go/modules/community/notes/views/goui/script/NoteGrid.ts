import {column, datasourcestore, DataSourceStore, datetimecolumn, t, Table} from "@intermesh/goui";
import {NoteDialog} from "./NoteDialog";
import {noteDS} from "./Index.js";
import {customFields, principalDS} from "@intermesh/groupoffice-core";

export class NoteGrid extends Table<DataSourceStore> {
	constructor() {
		super(
			datasourcestore({
				queryParams: {limit: 20},
				dataSource: noteDS,
				sort: [{
					property: "name"
				}],
				relations: {
					modifier: {dataSource: principalDS, path: "modifiedBy"},
					creator: {dataSource: principalDS, path: 'createdBy'}
				}
			}),
			[
				column({
					header: t("ID"),
					id: "id",
					sortable: true,
					hidden: true,
					width: 80,
					resizable: true
				}),
				column({
					header: t("Name"),
					id: "name",
					sortable: true,
					resizable: true,
				}),
				datetimecolumn({
					header: t("Created at"),
					id: "createdAt",
					sortable: true,
					resizable: true,
					hidden: true
				}),
				datetimecolumn({
					header: t("Modified at"),
					id: "modifiedAt",
					sortable: true,
					resizable: true,
				}),
				column({
					id: "creator/name",
					header: t("Created by"),
					width: 160,
					sortable: true,
					resizable: true,
					hidden: true
				}),
				column({
					id: "modifier/name",
					header: t("Modified by"),
					width: 160,
					sortable: true,
					resizable: true,
					hidden: true
				}),
			].concat(...customFields.getTableColumns("Note"))
		);

		this.scrollLoad = true;

		this.on("rowdblclick", async ({target, storeIndex}) => {
			const dlg = new NoteDialog();
			dlg.show();
			await dlg.load(target.store.get(storeIndex)!.id);
		});

		this.on("delete", async () => {
			const ids = this.rowSelection!.getSelected()!.map(row => row.record.id);

			await noteDS.confirmDestroy(ids);
		});

		this.fitParent = true;
	}
}