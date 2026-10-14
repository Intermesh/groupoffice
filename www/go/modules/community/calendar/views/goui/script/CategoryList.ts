import {
	btn,
	CheckboxField,
	checkboxselectcolumn,
	checkboxSelectColumnRenderer,
	column,
	Component,
	ComponentEventMap,
	menu,
	menucolumn,
	table,
	Table
} from "@intermesh/goui";
import {jmapds, modules} from "@intermesh/groupoffice-core";
import {categoryStore, t} from "./Index.js";
import {CategoryWindow} from "./CategoryWindow.js";

export interface CategoryListEventMap extends ComponentEventMap {
	/**
	 * Fires when the selected categories change
	 */
	change: {ids: string[]}
}

/**
 * Category selection used to filter the events
 */
export class CategoryList extends Component<CategoryListEventMap> {

	private selected: {[id: string]: boolean} = {};

	list: Table

	constructor(store = categoryStore) {
		super();

		const rights = modules.get("community", "calendar")!.userRights;

		this.list = table({
			store,
			headers: false,
			cls: 'no-row-lines',
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					selectionchange: ({selected}) => this.onSelectionChange(selected.map(r => String(r.record.id)))
				}
			},
			columns: [
				checkboxselectcolumn({
					renderer: (val, record, td, tbl, storeIndex, col) => {
						const cb = checkboxSelectColumnRenderer(val, record, td, tbl, storeIndex, col) as CheckboxField;
						cb.color = "#" + record.color;
						return cb;
					}
				}),
				column({
					id: 'name',
					resizable: false,
					renderer: (_v, record) => String(record.name ?? '').htmlEncode()
				}),
				...(rights.mayChangeCategories ? [menucolumn({
					menu: menu({
							listeners: {
								show: ({target}) => {
									const record = store.get(target.dataSet.rowIndex)!;
									target.findChild("edit")!.disabled = !record.myRights.mayAdmin;
									target.findChild("delete")!.disabled = !record.myRights.mayAdmin;
								}
							}
						},
						btn({itemId: "edit", icon: 'edit', text: t('Edit'), handler: async (me) => {
							const record = store.get(me.parent!.dataSet.rowIndex)!;
							const dlg = new CategoryWindow();
							await dlg.load(record.id);
							dlg.show();
						}}),
						btn({itemId: "delete", icon: 'delete', text: t('Delete'), handler: (me) => {
							const record = store.get(me.parent!.dataSet.rowIndex)!;
							void jmapds("CalendarCategory").confirmDestroy([record.id]);
						}})
					)
				})] : [])
			],
			listeners: {
				render: ({target}) => {
					target.store.on('load', ({target: s}) => {
						// The store reloads when visible calendars change. Restore the selection of the remaining categories.
						const records = s.data.filter((r: any) => this.selected[r.id]);
						Object.keys(this.selected).forEach(id => {
							if (!records.some((r: any) => r.id == id)) {
								// no longer available, e.g. calendar was hidden
								delete this.selected[id];
							}
						});
						target.rowSelection!.replace(...records);
					});
					void target.store.load();
				}
			}
		});

		this.items.add(this.list);
	}

	/**
	 * The selected category ids
	 */
	get value() {
		return Object.keys(this.selected);
	}

	private onSelectionChange(ids: string[]) {
		const before = this.value;

		// only categories that are loaded can be (de)selected, keep the others
		this.list.store.forEach(rec => delete this.selected[rec.id]);
		ids.forEach(id => this.selected[id] = true);

		const after = this.value;
		if (before.length !== after.length || before.some(id => !this.selected[id])) {
			this.fire('change', {ids: after});
		}
	}
}
