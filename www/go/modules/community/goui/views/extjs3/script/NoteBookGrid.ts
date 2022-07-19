import {column, Config, JmapStore, jmapstore, t, Table} from "goui.js";

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