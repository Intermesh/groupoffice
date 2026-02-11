import {btn, column, datasourcestore, DataSourceStore, menu, t, Table} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {LdapAuthServerDialog} from "./LdapAuthServerDialog";

export class LdapAuthServerTable extends Table<DataSourceStore> {
	constructor() {

		const store = datasourcestore({
			dataSource: jmapds("LdapAuthServer"),
			sort: [{property: "hostname", isAscending: true}],
		});

		const columns = [

			column({
				id: "hostname",
				header: t("Hostname", "community", "ldapauthenticator"),
				sortable: true
			}),
			column({
				id: "id",
				header: "ID",
				hidable: true,
				width: 120,
			}),
			column({
				id: "protocolVersion",
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
									const dlg = new LdapAuthServerDialog();
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
									void jmapds("LdapAuthServer").confirmDestroy([v]);
								}
							}))
					})
				}
			})
		];
		super(store, columns);

		this.on("rowdblclick", async ({storeIndex}) => {
			const dlg = new LdapAuthServerDialog();
			dlg.show();
			await dlg.load(this.store.get(storeIndex)!.id);
		});
	}

}
