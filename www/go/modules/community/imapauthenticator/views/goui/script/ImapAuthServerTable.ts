import {btn, column, comp, datasourcestore, DataSourceStore, menu, t, Table, Window} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {ImapAuthServerDialog} from "./ImapAuthServerDialog";

export class ImapAuthServerTable extends Table<DataSourceStore> {
	constructor() {

		const store = datasourcestore({
			dataSource: jmapds("ImapAuthServer"),
			sort: [{property: "imapHostName", isAscending: true}],
		});

		const columns = [

			column({
				id: "imapHostname",
				header: t("Hostname", "community", "imapauthenticator"),
				sortable: true
			}),
			column({
				id: "id",
				header: "",
				width: 50,
				sticky: true,
				sortable: false,
				renderer: (v, record) => {
					return btn({
						icon: "more_vert",
						menu: menu({},
							btn({
								text: "Edit",
								icon: "edit",
								handler: () => {
									const dlg = new ImapAuthServerDialog();
									dlg.on("close", () => {
										void store.reload();
									})
									dlg.load(v).then(() => {
										dlg.show();
									})
								}
							}),
							btn({
								text: "Delete",
								icon: "delete",
								handler: () => {
									void jmapds("ImapAuthServer").confirmDestroy([v]);
								}
							}))
					})
				}
			})
		];
		super(store, columns);

		this.on("rowdblclick", async ({storeIndex}) => {
			const dlg = new ImapAuthServerDialog();
			dlg.show();
			await dlg.load(this.store.get(storeIndex)!.id);
		});
	}

}
