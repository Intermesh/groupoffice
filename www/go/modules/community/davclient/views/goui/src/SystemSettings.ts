import {
	btn,
	column, comp,
	Component, datasourcestore,
	h3,
	t, table, tbar
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {AccountWindow} from "./AccountWindow.js";

export class SystemSettings extends Component {


	constructor() {
		super();

		this.items.add(comp({cls:'fit'},
			tbar({}, h3(t('DAV Accounts')), '->',
				btn({icon:'add',handler: () => { (new AccountWindow()).show()}})
			),
			table({
				fitParent:true,
				store: datasourcestore({
					dataSource: jmapds('DavAccount'),
				}),
				columns: [
					column({id:'id', header:'id'}),
					column({id:'name', header:'name'}),
					column({id:'id', header:'action', renderer: (v) => {
						return btn({text:'Sync', handler:() => {
								client.jmap('DavAccount/sync', {accountId:v}).then((response)=> {
									console.log(response);
								});
							}
						})
					}})
				],
				listeners: {
					rowdblclick:(tbl, storeIndex) => {
						const d = new AccountWindow();
						d.show();
						void d.load(tbl.store.get(storeIndex)!.id!);
					},

					delete: async (tbl) => {
						const ids = tbl.rowSelection!.selected.map(index => tbl.store.get(index)!.id!);
						await jmapds("DavAccount").confirmDestroy(ids);
					},
					render: tbl => { tbl.store.load(); }
				}
			})
		));
	}
}