import {
	btn,
	column,
	Component,
	datasourcestore,
	EntityID,
	hr,
	menu,
	mstbar,
	searchbtn,
	t,
	table,
	tbar
} from "@intermesh/goui";
import {OIDConnectClientDS} from "@intermesh/community/oidc";
import {OIDConnectClientDialog} from "./OIDConnectClientDialog.js";
import {jmapds} from "@intermesh/groupoffice-core";

export class Settings extends Component {

	constructor() {
		super();


		this.cls = "vbox";

		const store = datasourcestore({
			dataSource: OIDConnectClientDS,
			queryParams: {
				limit: 50,
				filter: {
				}
			},
			sort: [{property: "name", isAscending: true}]
		});

		const tbl = table({
			flex: 1,
			cls: "bg-lowest",
			store,
		 	rowSelectionConfig: {
				multiSelect: true
		 	},
			columns: [
				column({
					id: "name",
					resizable: true,
					header: t("Name"),
					sortable: true
				}),

				column({
					width: 48,
					id: "btn",
					sticky: true,
					renderer: (columnValue: any, record, td, table, rowIndex) => {
						return btn({
							icon: "more_vert",
							menu: menu({},
								btn({
									icon: "edit",
									text: t("Edit"),
									handler: async (btn) => {
										const book = table.store.get(rowIndex)!;
										this.openOIDConnectClientDialog(book.id);
									}
								}),
								hr(),
								btn({
									icon: "delete",
									text: t("Delete"),
									handler: async (btn) => {
										const book = table.store.get(rowIndex)!;
										void OIDConnectClientDS.confirmDestroy([book.id]);
									}
								})

							)
						})
					}
				}),
			]
		})

		this.items.add(
			tbar({},
				'->',
				searchbtn({
					listeners: {
						input: ( {text}) => {
							tbl!.store.setFilter("text", {text}).load()
						}
					}
				}),
				btn({
					cls: "primary filled",
					icon: "add",
					text: t("Add"),
					handler: async (_btn) => {
						await this.openOIDConnectClientDialog();
					}
				}),

				mstbar({table: tbl},
					"->",
					btn({
						icon: "delete",
						handler: async (btn) => {

							const ids = tbl.rowSelection!.getSelected().map(row => row.record.id);

							const result = await OIDConnectClientDS.confirmDestroy(ids);

							if(result != false) {
								btn.parent!.hide();
							}

						}
					})
				)

			),

			tbl
		);

		tbl.on("rowdblclick", async ({storeIndex}) => {
			await this.openOIDConnectClientDialog(store.get(storeIndex)!.id);
		});

		tbl.on("delete", async ({target}) => {
			const ids = target.rowSelection!.getSelected().map(row => row.id);
			await OIDConnectClientDS
				.confirmDestroy(ids);
		});

		this.on("render", () => {
			store.load();
		})
	}

	private async openOIDConnectClientDialog(id?: EntityID): Promise<void> {
		const dlg = new OIDConnectClientDialog();
		dlg.show();
		if (id) {
			await dlg.load(id);
		}
	}

	onSubmit() {
	}
}