import {
	AbstractSettingsPanel,
	AclLevel,
	AppSettingsPanel,
	client, LegacyApi,
	userDS,
	userSettingsPanels
} from "@intermesh/groupoffice-core";
import {
	btn,
	column,
	comp,
	datasourcestore,
	EntityID,
	searchbtn,
	store,
	t,
	table,
	Table,
	tbar,
	Window
} from "@intermesh/goui";
import {templateDS} from "./Index";
import {EmailTemplateDialog} from "./EmailTemplateDialog";

export class EmailTemplatesSettingsPanel extends AppSettingsPanel {

	private tbl: Table;
	private legacyApi: LegacyApi;

	constructor() {
		super();

		this.cls = "fit";
		this.title = t("E-mail templates");
		this.legacyApi = new LegacyApi("email", "template");

		this.tbl = table({
			cls: "fit",
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
						const win = new EmailTemplateDialog();
						win.form.on("submit", ({}) => {
							void this.loadTblStore();
						});
						win.title = record.name;
						win.load(record.id);
						win.show();
					}
				}
			},
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

		this.items.add(tbar({
					cls: "border-bottom"
				},
				btn({
					icon: "add",
					text: t("Add"),
					handler: () => {
						const win = new EmailTemplateDialog();
						win.form.on("submit", ({}) => {
							void this.loadTblStore();
						});
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
							selectedIds.forEach((id: EntityID) => {
								promises.push(this.legacyApi.delete(id));
							});
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

					}
				}),
				"->",
				searchbtn()
			),
			this.tbl);

		this.on("render", async () => {
			void this.loadTblStore();
		});
	}

	private async loadTblStore() {
		const data = await this.legacyApi.store();
		if (data.results.length) {
			this.tbl.store.loadData(data.results, false);
		}
	}
}
