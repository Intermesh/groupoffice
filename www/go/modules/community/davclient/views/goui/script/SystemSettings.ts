import {
	btn, Format,
	column, comp,
	Component, datasourcestore,
	h3,
	t, table, tbar, Window, win
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
								this.mask();
								client.jmap('DavAccount/sync', {accountId:v}).then((response)=> {
									store.reload();
								}).catch((err) => {
									me.disabled = false;
									Window.error(err);
								}).finally(() => {
									this.unmask();
								})
							}
						})
					}})
				],
				rowSelectionConfig: {
					multiSelect: false,
				},
				listeners: {
					rowdblclick:(tbl, storeIndex) => {
						const d = new AccountWindow();
						d.show();
						void d.load(tbl.store.get(storeIndex)!.id!);
					},

					delete: async (tbl) => {
						const ids: string[] = tbl.rowSelection!.getSelected().map(row => row.id);
						// ask to keep data
						const w = win({
								modal: true,
								title: t('Keep calendar data?'),
								closable: false,
								width: 600,
								listeners: {focus: (w) => {
									w.findChild("yes")!.focus();
								}}
							},

							comp({cls: "pad", html: t('Do you want to keep the synchronised calendars or delete those as well?')
							 + '<br>' + t('If only the account is deleted the calendars can still be deleted manually')}),

							tbar({},
								btn({text:t('Cancel'), handler: () => {w.close();} }),
								'->',
								btn({itemId: "no", text: t("Keep calendar data"), handler: () => {
										jmapds("DavAccount").setParams = {keepData:true};
										ids.map(id => jmapds("DavAccount").destroy(id));
										w.close();
									}
								}),

								btn({itemId: "yes", text: t("Delete all"), cls: "filled primary", handler: () => {
										jmapds("DavAccount").setParams = {keepData:false};
										ids.map(id => jmapds("DavAccount").destroy(id));
										w.close();
									}
								})
							)
						);

						w.show();
					},
					render: tbl => { tbl.store.load(); }
				}
			})
		));
	}
}