import {
	column,
	Config, createComponent,
	datasourcestore,
	DataSourceStore,
	t, Table,
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class TasklistGrid extends Table<DataSourceStore> {
	constructor() {
		const store = datasourcestore({
			dataSource: jmapds("TaskList"),
			filters: {
				role: {
					role: "list"
				},
				subscribed: {
					isSubscribed: true
				}
			},
			queryParams: {
				limit: 0
			},
			sort: [{property: "name", isAscending: true}]
		});

		const columns = [
			column({
				header: t("Name"),
				id: "name"
			})
		];

		super(store, columns);
	}
}

export const tasklistgrid = (config: Config<TasklistGrid>) => createComponent(new TasklistGrid(), config);