import {
	btn,Format,
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

		const store = datasourcestore({
			dataSource: jmapds('DavAccount'),
		});

		this.items.add(comp({cls:'fit'},
			tbar({}, h3(t('DAV Accounts')), '->',
				btn({icon:'add',handler: () => { (new AccountWindow()).show()}})
			),
			table({
				fitParent:true,
				store,
				columns: [
					column({id:'id', header:'id'}),
					column({id:'name', header:'name'}),
					column({id:'lastSync', header:'Last Sync',renderer: (date: string) => Format.smartDateTime(date, true)}),
					column({id:'id', header:'action', renderer: (v) => {
						return btn({text:'Sync', handler:(me) => {
								me.disabled = true;
								client.jmap('DavAccount/sync', {accountId:v}).then((response)=> {
									store.reload();
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
						const ids = tbl.rowSelection!.getSelected().map(row => row.id);
						await jmapds("DavAccount").confirmDestroy(ids);
					},
					render: tbl => { tbl.store.load(); }
				}
			})
		));
	}
}