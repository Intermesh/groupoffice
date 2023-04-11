import {
	column,
	Config,
	createComponent,
	datasourcestore,
	DataSourceStore,
	jmapds,
	t,
	Table
} from "@intermesh/goui";


export class NoteBookGrid extends Table<DataSourceStore> {

	constructor() {

		super(
			datasourcestore({
				dataSource: jmapds("NoteBook"),
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