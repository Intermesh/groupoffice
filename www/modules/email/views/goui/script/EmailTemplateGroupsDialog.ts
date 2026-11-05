import {btn, column, searchbtn, store, t, table, Table, tbar, Window} from "@intermesh/goui";
import {AclLevel, LegacyApi} from "@intermesh/groupoffice-core";

export class EmailTemplateGroupsDialog extends Window {
	private readonly tbl: Table;
	private legacyApi: LegacyApi;
	private origData = [];

	constructor() {
		super();
		this.title = t("Groups");
		this.closable = true;
		this.resizable = false;

		let win: any = undefined;
		this.legacyApi = new LegacyApi("email", "templateGroup");

		this.items.add(tbar({},
				btn({
					icon: "add", text: t("Add"), handler: () => {
						if (!win) {
							win = new GO.email.TemplateGroupDialog();
						}
						win.on("save", () => {
							void this.loadTblStore();
						});
						win.on("hide", () => {
							win = undefined;
						});
						win.show();

					}
				}),
				btn({
					icon: "delete", text: t("Delete"), handler: async () => {
						const selectedIds = this.tbl.rowSelection!.getSelected().map((row) => row.id);
						if (!selectedIds.length) {
							return
						}

						const q = `Are you sure you want to delete the selected item${selectedIds.length !== 1 ? 's' : ''}?`;
						const yes = await Window.confirm(t(q));

						if (yes) {
							const promises: any[] = [];
							promises.push(this.legacyApi.delete(selectedIds));
							this.mask();
							await Promise.all(promises);
							this.unmask();
							void this.loadTblStore();
						}
					}
				}),
				"->",
				searchbtn({
					listeners: {
						input: ({text}) => {
							const filtered = this.origData.filter((r: any) => {
								return !text || r.name.toLowerCase().indexOf(text.toLowerCase()) > -1;
							});
							this.tbl.store.loadData(filtered, false)
						}
					}
				})
			),
			this.tbl = table({
				store: store({
					sort: [{property: "name", isAscending: true}]
				})
				,
				rowSelectionConfig: {
					multiSelect: true
				},
				listeners: {
					rowdblclick: ({target, storeIndex}) => {
						const record = target.store.get(storeIndex);
						if (record) {
							if (!win) {
								win = new GO.email.TemplateGroupDialog();
							}
							win.on("save", () => {
								void this.loadTblStore();
							});
							win.on("hide", () => {
								win = undefined;
							});
							win.show(record.id);
						}
					}
				},
				columns: [
					column({
						id: "name",
						header: t("Name"),
						sortable: false,
						hidable: false
					})
				]
			})
		);
		this.on("render", () => {
				this.loadTblStore();
			}
		);
	}

	private async loadTblStore() {
		const data = await this.legacyApi.store(AclLevel.READ, {limit: "0"});
		if (data.results.length) {
			this.tbl.store.loadData(data.results, false);
			this.origData = data.results;
		}
	}
}
