import {column, Config, createComponent, datasourcestore,DataSourceStore, JmapDataSource, t, Table} from "@intermesh/goui";


export class NoteBookGrid extends Table<DataSourceStore> {

	constructor() {

		super(
			datasourcestore({
				dataSource: JmapDataSource.store("NoteBook"),
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