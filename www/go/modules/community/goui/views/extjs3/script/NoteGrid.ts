import {column, datetimecolumn, Table} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {Store} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {NoteDialog} from "./NoteDialog.js";
import {JmapStore, jmapstore} from "../../../../../../../views/Extjs3/goui/script/api/JmapStore.js";

export interface NoteGrid {
	store : JmapStore
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