
import {NoteDialog} from "./NoteDialog.js";
import {Table} from "@goui/component/table/Table.js";
import {JmapStore, jmapstore} from "@goui/jmap/JmapStore.js";
import {t} from "@goui/Translate.js";
import {column, datetimecolumn} from "@goui/component/table/TableColumns.js";

export interface NoteGrid {
	store: JmapStore
}

export class NoteGrid extends Table {

	constructor() {

		super(
			jmapstore({
				entity: "Note",
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