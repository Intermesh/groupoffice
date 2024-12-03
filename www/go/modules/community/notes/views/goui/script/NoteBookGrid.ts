import {
	BaseEntity,
	column,
	Config,
	createComponent,
	datasourcestore,
	DataSourceStore,
	t,
	Table
} from "@intermesh/goui";
import {JmapDataSource, jmapds} from "@intermesh/groupoffice-core";

interface NoteBook extends BaseEntity {
	name: string
}

export class NoteBookGrid extends Table<DataSourceStore> {

	constructor() {
		const store = datasourcestore<JmapDataSource<NoteBook>>({
			dataSource: jmapds("NoteBook"),
			queryParams: {
				limit: 0,
				filter: {
					permissionLevel: 5
				}
			},
			sort: [{
				property: "name"
			}]
		});

		const columns = [
			column({
				header: t("Name"),
				id: "name",
				sortable: true,
			})
		];

		super(store, columns);
	}

}

export const notebookgrid = (config: Config<NoteBookGrid>) => createComponent(new NoteBookGrid(), config);