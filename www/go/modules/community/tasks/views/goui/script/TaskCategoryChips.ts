import {
	AutocompleteChips,
	checkboxselectcolumn,
	column, createComponent, DataSourceStore,
	datasourcestore, FieldConfig, Filter, Table,
	table
} from "@intermesh/goui";
import {taskCategoryDS} from "./Index.js";

export class TaskCategoryChips extends AutocompleteChips<Table<DataSourceStore>> {
	constructor() {
		super(
			table({
				headers: false,
				fitParent: true,
				store: datasourcestore({
					dataSource: taskCategoryDS,
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
			const record = await taskCategoryDS.single(value);
			chip.text = record!.name;
		};

		this.on("autocomplete", ({target, input}) => {
			(target.list.store.filter as Filter).text = input;
			void target.list.store.load();
		});

		this.on("render", ({target}) => {
			void target.list.store.load();
		});
	}
}

export type TaskCategoryChipsConfig = Omit<FieldConfig<TaskCategoryChips>, "list">;

export const taskcategorychips = (config?: TaskCategoryChipsConfig) => createComponent(new TaskCategoryChips(), config);