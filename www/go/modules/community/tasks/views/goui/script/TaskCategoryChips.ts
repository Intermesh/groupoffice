import {
	AutocompleteChips,
	checkboxselectcolumn,
	column, createComponent, DataSourceStore,
	datasourcestore, FieldConfig, Filter, Table,
	table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class TaskCategoryChips extends AutocompleteChips<Table<DataSourceStore>> {
	constructor() {
		super(
			table({
				headers: false,
				fitParent: true,
				store: datasourcestore({
					dataSource: jmapds("TaskCategory"),
					queryParams: {
						limit: 0
					}
				}),
				rowSelectionConfig: {
					multiSelect: true
				},
				columns: [
					checkboxselectcolumn(),
					column({
						header: "name",
						id: "name",
						sortable: true,
						resizable: true
					})
				]
			})
		);

		this.chipRenderer = async (chip, value) => {
			const record = await jmapds("TaskCategory").single(value);
			chip.text = record!.name;
		};

		this.on("autocomplete", (field, input) => {
			(field.list.store.filter as Filter).text = input;
			void field.list.store.load();
		});

		this.on("render", (field) => {
			void field.list.store.load();
		});
	}
}

export type TaskCategoryChipsConfig = Omit<FieldConfig<TaskCategoryChips>, "list">;

export const taskcategorychips = (config?: TaskCategoryChipsConfig) => createComponent(new TaskCategoryChips(), config);