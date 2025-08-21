import {
	column,
	Config,
	createComponent,
	datasourcestore,
	DataSourceStore,
	t,
	Table
} from "@intermesh/goui";
import {AclLevel, client} from "@intermesh/groupoffice-core";
import {noteBookDS} from "./Index.js";

export class NoteBookGrid extends Table<DataSourceStore> {

	constructor() {
		const store = datasourcestore({
			dataSource: noteBookDS,
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
			filters: {
				permissionLevel: {permissionLevel: AclLevel.READ}
			},
			queryParams: {
				limit: 0
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