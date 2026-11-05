import {
	AclLevel,
	AppSettingsPanel,
	LegacyApi,
	userDS,
} from "@intermesh/groupoffice-core";
import {
	btn,
	column,
	comp,
	searchbtn,
	store,
	t,
	table,
	Table,
	tbar,
	Window
} from "@intermesh/goui";
import {EmailTemplateGroupsDialog} from "./EmailTemplateGroupsDialog";

export class EmailTemplatesSettingsPanel extends AppSettingsPanel {

	private readonly tbl: Table;
	private legacyApi: LegacyApi;
	private origData = [];

	constructor() {
		super();
		let win:any = undefined;

		this.title = t("E-mail templates");
		this.legacyApi = new LegacyApi("email", "template");

		this.tbl = table({
			store: store({
				sort: [{property: "name", isAscending: true}]
			}),
			rowSelectionConfig: {
				multiSelect: true
			},
			listeners: {
				rowdblclick: ({target, storeIndex}) => {
					const record = target.store.get(storeIndex);
					if (record) {
						if (!win) {
							win = new GO.email.EmailTemplateDialog();
						}
						win.on("save", () => {
							void this.loadTblStore();
						});
						win.on("hide", () => {win = undefined;});
						win.show(record.id);
					}
				}
			},
			groupBy: "group_name",
			columns: [
				column({
					id: "name",
					header: t("Name"),
					sortable: false,
					hidable: false
				}),
				column({
					id: "user_id",
					header: t("Owver"),
					sortable: false,
					hidable: false,
					renderer: async (v) => {
						const u = await userDS.single(v);
						if (u) {
							return u.displayName;
						}
						return t("Unknown user");
					}
				}),
				column({
					sticky: true,
					width: 40,
					sortable: false,
					hidable: false,
					id: "btn",
					renderer: (v, record) => {
						if (record.permissionLevel < AclLevel.WRITE) {
							return comp({tagName: "i", cls: "icon", text: "lock"});
						}
						return "";
					}
				})
			]
		});

		this.items.add(tbar({},
				btn({
					icon: "add",
					text: t("Add"),
					handler: () => {
						if (!win) {
							win = new GO.email.EmailTemplateDialog();
						}
						win.on("save", ({}) => {
							void this.loadTblStore();
						});
						win.on("hide", () => {win = undefined;})
						win.show();
					}
				}),
				btn({
					icon: "delete",
					text: t("Delete"),
					handler: async () => {
						const selectedIds = this.tbl.rowSelection!.getSelected().map((row) => row.id);
						if (!selectedIds.length) {
							return
						}

						const q = `Are you sure you want to delete the selected item${selectedIds.length !== 1 ? 's': ''}?`;
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
				btn({
					icon: "list",
					text: "Groups",
					handler: () => {
						const w = new EmailTemplateGroupsDialog()
						w.show();
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
			this.tbl);

		this.on("render", async () => {
			void this.loadTblStore();
		});
	}

	private async loadTblStore() {
		const data = await this.legacyApi.store(AclLevel.READ, {limit: "0"});
		if (data.results.length) {
			this.tbl.store.loadData(data.results, false);
			this.origData = data.results;
		}
	}
}
