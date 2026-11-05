import {
	CheckboxField,
	checkboxselectcolumn,
	checkboxSelectColumnRenderer,
	column,
	Component,
	ComponentEventMap,
	store as localStore,
	table,
	Table
} from "@intermesh/goui";
import {CalendarAdapter} from "./CalendarAdapter.js";

export interface ProviderListEventMap extends ComponentEventMap {
	/**
	 * Fires after a provider was enabled or disabled
	 */
	change: {}
}

/**
 * Other (non calendar) providers like tasks and birthdays. Same selection style as the calendar list.
 */
export class ProviderList extends Component<ProviderListEventMap> {

	list: Table

	constructor(private adapter: CalendarAdapter) {
		super();

		const providerStore = localStore({});
		providerStore.loadData(Object.keys(adapter.providers).filter(name => adapter.providers[name].checkbox).map(name => {
			const p = adapter.providers[name];
			return {id: name, label: p.checkbox!.label, color: p.checkbox!.color, enabled: p.enabled};
		}), false);

		this.list = table({
			store: providerStore,
			headers: false,
			cls: 'no-row-lines',
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					selectionchange: ({selected}) => {
						const on = selected.map(s => String(s.record.id));
						let changed = false;
						providerStore.forEach(rec => {
							const enabled = on.indexOf(rec.id) > -1;
							if (enabled !== !!adapter.providers[rec.id].enabled) {
								adapter.providers[rec.id].checkbox!.onChange?.(enabled, rec.id);
								changed = true;
							}
						});
						if (changed) {
							this.fire('change', {});
						}
					}
				}
			},
			columns: [
				checkboxselectcolumn({
					renderer: (val, record, td, tbl, storeIndex, col) => {
						const cb = checkboxSelectColumnRenderer(val, record, td, tbl, storeIndex, col) as CheckboxField;
						cb.color = record.color;
						return cb;
					}
				}),
				column({
					id: 'label',
					resizable: false,
					renderer: (_v, record) => String(record.label ?? '').htmlEncode()
				})
			]
		});

		this.list.on('render', () => {
			this.list.rowSelection!.replace(...providerStore.data.filter((r: any) => r.enabled));
		});

		this.items.add(this.list);
	}
}
