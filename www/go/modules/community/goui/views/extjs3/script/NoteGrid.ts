import {column, datetimecolumn, JmapStore, jmapstore, t, Table} from "goui.js";
import {NoteDialog} from "./NoteDialog.js";

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
					property: "name",
					sortable: true
				}),

				datetimecolumn({
					header: t("Created At"),
					property: "createdAt",
					sortable: true
				})
			]
		);

		this.on("rowdblclick", async (table, rowIndex, ev) => {
			const dlg = new NoteDialog();
			dlg.show();
			await dlg.load(table.store.get(rowIndex).id);

		})
	}

}