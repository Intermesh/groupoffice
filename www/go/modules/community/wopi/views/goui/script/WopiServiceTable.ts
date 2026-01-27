import {btn, column, comp, datasourcestore, DataSourceStore, menu, t, Table, Window} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {WopiServiceDialog} from "./WopiServiceDialog";
import {WopiServiceDS} from "@intermesh/community/wopi";
import {noteDS} from "@intermesh/community/notes";

export class WopiServiceTable extends Table<DataSourceStore> {
	constructor() {

		const store = datasourcestore({
			dataSource: jmapds("WopiService"),
			sort: [{property: "name", isAscending: true}],
		});

		const columns = [
			column({
				id: "type",
				header: "",
				sortable: false,
				width: 50,
				renderer: (v) => {
					const iconCls = v === "collabora" ? "ic-collabora" : "ic-office-online";
					return comp({tagName: "i", cls: "icon " + iconCls});
				}
			}),
			column({
				id: "name",
				header: t("name"),
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
									const dlg = new WopiServiceDialog();
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
									void jmapds("WopiService").confirmDestroy([v]);
								}
							}))
					})
				}
			})
		];
		super(store, columns);

		this.on("rowdblclick", async ({storeIndex}) => {
			const dlg = new WopiServiceDialog();
			dlg.show();
			await dlg.load(this.store.get(storeIndex)!.id);
		});
	}

}
