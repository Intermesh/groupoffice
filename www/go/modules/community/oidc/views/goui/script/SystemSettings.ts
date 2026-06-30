import {
	btn,
	column, Component,
	datasourcestore,
	EntityID,
	Fieldset,
	h3, h4,
	hr,
	menu,
	menucolumn,
	mstbar,
	t,
	table,
	tbar
} from "@intermesh/goui";

import {OIDConnectClientDialog} from "./OIDConnectClientDialog.js";
import {OIDConnectClientDS} from "./Index.js";

/*
@deprocated - the GOUI System settings will use Settings instead
 */
export class SystemSettings extends Component {

	constructor() {
		super();

		this.cls = "card vbox";

		const store = datasourcestore({
			dataSource: OIDConnectClientDS,
			sort: [{property: "name", isAscending: true}]
		});

		const tbl = table({
			flex: 1,
			cls: "bg-lowest",
			store,
		 	rowSelectionConfig: {
				multiSelect: true
		 	},
			listeners: {
				delete: async () =>  {
					const ids = tbl.rowSelection!.getSelected()!.map(row => row.record.id);
					await OIDConnectClientDS.confirmDestroy(ids);
				},
				rowdblclick: async ({storeIndex}) => {
					this.openOIDConnectClientDialog(store.get(storeIndex)!.id);
				},
				render: () => {
					void store.load();
				}
			},
			columns: [
				column({
					id: "name",
					resizable: true,
					header: t("Name"),
					sortable: true
				}),

				menucolumn({
					menu: menu({},
						btn({
							text: t("Open"),
							icon: "open_in_new",
							handler: (b) => {
								const book = tbl.store.get(b.parent!.dataSet.rowIndex)!;
								this.openOIDConnectClientDialog(book.id);
							}
						}),
						hr(),
						btn({
							icon: "delete",
							text: t("Delete"),
							handler: async (b) => {
								tbl.delete();
							}
						})
					)
				})
			]
		})

		this.items.add(
			tbar({},
				h4(t("OpenID Connect clients")),
				'->',

				btn({
					cls: "primary filled",
					icon: "add",
					text: t("Add"),
					handler: async () => {
						this.openOIDConnectClientDialog();
					}
				}),

				mstbar({table: tbl},
					"->",
					btn({
						icon: "delete",
						handler: async (btn) => {

							tbl.delete();
							btn.parent!.hide();

						}
					})
				)
			),

			tbl
		);
	}

	private openOIDConnectClientDialog(id?: EntityID) {
		const dlg = new OIDConnectClientDialog();
		dlg.show();
		if (id) {
			void dlg.load(id);
		}
		return dlg;
	}

	onSubmit() {
	}
}