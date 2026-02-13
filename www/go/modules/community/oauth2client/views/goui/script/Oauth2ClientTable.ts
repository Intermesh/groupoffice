import {btn, column, comp, datasourcestore, DataSourceStore, menu, t, Table} from "@intermesh/goui";
import {Oauth2ClientDialog} from "./Oauth2ClientDialog";
import {DefaultClientDS, Oauth2ClientDS} from "@intermesh/community/oauth2client";
import {jmapds} from "@intermesh/groupoffice-core";

export class Oauth2ClientTable extends Table {
	constructor() {
		const store = datasourcestore({
			dataSource: Oauth2ClientDS,
			sort: [{property: "name", isAscending: true}]
		});

		const columns = [
			column({
				id: "name",
				header: t("Name"),
				sortable: true
			}),
			column({
				id: "clientId",
				header: t("Client Id"),
				sortable: true
			}),
			column({
				id: "clientSecret",
				header: t("Client Secret"),
				hidden: true
			}),
			column({
				header: t("Project ID"),
				id: "projectId",
			}),
			column({
				header: t("Provider"),
				id: "defaultClientId",
				width: 90,
				renderer: async (v) => {
					// const d = await DefaultClientDS.single(v);
					// return d ? d.name : "--";
					let cls = "ic-";
					switch (parseInt(v)) {
						case 1:
							cls += "google";
							break;
						case 2:
							cls += "microsoft";
							break;
						default:
							cls += "";

					}
					return comp({tagName: "i", cls: "icon "+cls});
				}
			}),
			column({
				width: 90,
				id: "openId",
				header: "Open ID",
				renderer: (v) =>
					v ? comp({tagName: "i", cls: "icon go-module-icon-openid"}) : ""
			}),
			column({
				header: "",
				id: "id",
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
									const dlg = new Oauth2ClientDialog();
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
									void Oauth2ClientDS.confirmDestroy([v]);
								}
							}))
					})
				}
			})
		];
		super(store, columns);

		this.on("rowdblclick", async ({storeIndex}) => {
			const dlg = new Oauth2ClientDialog();
			dlg.show();
			await dlg.load(this.store.get(storeIndex)!.id);
		});
	}
}