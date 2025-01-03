import {btn, column, comp, datasourcestore, DataSourceStore, t, table, Table, tbar, Window} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {CategoryDialog} from "./CategoryDialog.js";


export class ManageCategoriesWindow extends Window {
	private readonly store: DataSourceStore;
	private readonly table: Table

	constructor() {
		super();

		this.title = t("Administrate categories");
		this.width = 600;
		this.height = 500;

		this.stateId = "manage-categories-grid";
		this.maximizable = true;
		this.resizable = true;

		this.store = datasourcestore({
			dataSource: jmapds("BookmarksCategory"),
			relations: {
				creator: {
					path: "createdBy",
					dataSource: jmapds("Principal")
				}
			}
		});

		this.table = table({
			cls: "bg-lowest",
			fitParent: true,
			rowSelectionConfig: {
				multiSelect: false
			},
			columns: [
				column({
					id: "name",
					header: t("Name"),
					resizable: true,
					sortable: true
				}),
				column({
					id: "creator",
					header: t("Owner"),
					renderer: (v) => {
						return v ? v.name : "-";
					},
					resizable: true,
					sortable: false
				})
			],
			listeners: {
				rowdblclick: (list, storeIndex) => {
					const dlg = new CategoryDialog();

					dlg.show();

					void dlg.load(list.store.get(storeIndex)!.id);
				}
			},
			store: this.store
		});

		this.items.add(
			tbar({
					cls: "border-bottom"
				},
				btn({
					icon: "add",
					text: t("Add"),
					handler: () => {
						const dlg = new CategoryDialog();

						dlg.show();
					}
				}),
				btn({
					icon: "delete",
					text: t("Delete"),
					handler: () => {
						if (this.table.rowSelection!.getSelected()[0]) {
							void jmapds("BookmarksCategory").confirmDestroy([this.table.rowSelection!.getSelected()[0].record.id]);
						}
					}
				})
			),
			comp({cls: "scroll", flex: 1}, this.table)
		)

		void this.store.load();
	}
}