import {column, datetimecolumn, Table} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {Store} from "../../../../../../../views/Extjs3/goui/script/data/Store.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {NoteDialog} from "./NoteDialog.js";
import {JmapStore, jmapstore} from "../../../../../../../views/Extjs3/goui/script/api/JmapStore.js";
import {Config} from "../../../../../../../views/Extjs3/goui/script/component/Observable.js";

export interface NoteBookGrid {
	store : JmapStore
}

export class NoteBookGrid extends Table {

	constructor() {

		const columns = [
			column({
				header: t("Name"),
				property: "name",
				sortable: true
			})
		];

		super(jmapstore({
			entity: "NoteBook",
			sort: [{
				property: "name"
			}]
		}), columns);
	}

}

export const notebookgrid = (config: Config<NoteBookGrid>) => Object.assign(new NoteBookGrid(), config);