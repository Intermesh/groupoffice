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
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";

interface NoteBook extends BaseEntity {
	name: string
}

export class NoteBookGrid extends Table<DataSourceStore> {

	constructor() {
		const store = datasourcestore<JmapDataSource<NoteBook>>({
			dataSource: jmapds("NoteBook"),
			listeners: {
				load: () => {
					const defaultNoteBookIds: any[] = [];

					if (!client.user.notesSettings.rememberLastItems) {
						defaultNoteBookIds.push(client.user.notesSettings.defaultNoteBookId);
					} else {
						defaultNoteBookIds.push(...client.user.notesSettings.lastNoteBookIds);
					}

					defaultNoteBookIds.forEach((id: any) => {
						const record = store.find((r, index, records) => r.id == id);

						if (record) {
							this.rowSelection!.add(record);
						}
					});
				}
			},
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