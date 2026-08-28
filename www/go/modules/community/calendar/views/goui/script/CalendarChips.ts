import {
	autocompletechips,
	AutoCompleteChipsConfig,
	checkboxselectcolumn,
	column,
	DataSourceStore,
	datasourcestore,
	t,
	Table,
	table
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export function calendarchips(config?: Partial<AutoCompleteChipsConfig<Table<DataSourceStore>> & {filter: Record<string, any>}>) {
	return autocompletechips({
		label: t("Calendars"),
		list: table({
			fitParent: true,
			headers: false,
			store: datasourcestore({
				dataSource: jmapds("Calendar"),
				filters:{
					default: config?.filter ?? {}
				}
			}),
			rowSelectionConfig: {
				multiSelect: true
			},
			columns: [
				checkboxselectcolumn(),
				column({
					header: t("Name"),
					id: "name",
					sortable: true,
					resizable: true
				})
			]
		}),
		chipRenderer: async (chip, value) => {
			const record = await jmapds("Calendar").single(value);
			chip.text = record.name;
		},
		pickerRecordToValue(field, record): any {
			return record.id;
		},
		listeners: {
			autocomplete: ({target, input}) => {
				target.list.store.setFilter("search", {text: input});
				void target.list.store.load();
			}
		},
		...config
	})
}