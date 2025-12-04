import {
	column, Config, createComponent,
	datasourcestore,
	DataSourceStore, t,
	Table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class TaskCategoryGrid extends Table<DataSourceStore> {
	constructor() {
		const store = datasourcestore({
			dataSource: jmapds("TaskCategory")
		});

		const columns = [
			column({
				id: "name",
				header: t("Name")
			})
		];

		super(store, columns);
	}
}

export const taskcategorygrid = (config: Config<TaskCategoryGrid>) => createComponent(new TaskCategoryGrid(), config);
