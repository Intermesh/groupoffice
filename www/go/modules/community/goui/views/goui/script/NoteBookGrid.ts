import {Table} from "@goui/component/table/Table.js";
import {JmapStore, jmapstore} from "@goui/jmap/JmapStore.js";
import {t} from "@goui/Translate.js";
import {Config, createComponent} from "@goui/component/Component.js";
import {column} from "@goui/component/table/TableColumns.js";


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
					id: "name",
					sortable: true
				})
			]
		);
	}

}

export const notebookgrid = (config: Config<NoteBookGrid>) => createComponent(new NoteBookGrid(), config);