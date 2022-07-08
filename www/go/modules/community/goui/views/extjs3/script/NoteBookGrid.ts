import {column, Table} from "../../../../../../../views/Extjs3/goui/script/component/Table.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {JmapStore, jmapstore} from "../../../../../../../views/Extjs3/goui/script/api/JmapStore.js";
import {Config} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";

export class NoteBookGrid extends Table<JmapStore> {

	constructor() {

		super(

			jmapstore({
				entity: "NoteBook",
				sort: [{
					property: "name"
				}]
			}),

			[
				column({
					header: t("Name"),
					property: "name",
					sortable: true
				})
			]
		);
	}

}

export const notebookgrid = (config: Config<NoteBookGrid>) => Object.assign(new NoteBookGrid(), config);