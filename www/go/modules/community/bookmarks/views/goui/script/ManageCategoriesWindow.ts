import {
	btn, Button,
	column,
	comp,
	datasourcestore,
	DataSourceStore,
	list,
	t,
	table,
	Table,
	tbar,
	Window
} from "@intermesh/goui";
import {CategoryDialog} from "./CategoryDialog.js";
import {bookmarksCategoryDS} from "./Index.js";
import {AclLevel, client, principalDS} from "@intermesh/groupoffice-core";


export class ManageCategoriesWindow extends Window {
	private readonly store: DataSourceStore;
	private readonly table: Table;
	private deleteBtn: Button;


	constructor() {
		super();

		this.title = t("Administrate categories");
		this.width = 600;
		this.height = 500;

		this.stateId = "manage-categories-grid";
		this.maximizable = true;
		this.resizable = true;

		this.store = datasourcestore({
			dataSource: bookmarksCategoryDS,
			sort: [{property: "name"}],
			relations: {
				creator: {
					path: "createdBy",
					dataSource: principalDS
				}
			}
		});

		this.table = table({
			cls: "bg-lowest",
			fitParent: true,
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					selectionchange: ({selected}) => {
						const record = selected.map((row) => row.record)[0];

						this.deleteBtn.disabled = record.permissionLevel < AclLevel.DELETE;
					}
				}
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
				rowdblclick: ({target, storeIndex}) => {
					const record = target.store.get(storeIndex)!;

					if (record.permissionLevel > AclLevel.WRITE) {
						const dlg = new CategoryDialog();
						dlg.show();
						void dlg.load(record.id);
					}

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
				this.deleteBtn = btn({
					icon: "delete",
					text: t("Delete"),
					disabled: true,
					handler: () => {
						if (this.table.rowSelection!.getSelected()[0]) {
							void bookmarksCategoryDS.confirmDestroy([this.table.rowSelection!.getSelected()[0].record.id]);
						}
					}
				})
			),
			comp({cls: "scroll", flex: 1}, this.table)
		)

		void this.store.load();
	}
}