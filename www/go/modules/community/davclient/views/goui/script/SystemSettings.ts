import {
	btn, Format,
	column, comp,
	Component, datasourcestore,
	h3,
	t, table, tbar, Window, win, hr, menu, EntityID
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
				cls: 'bg-lowest',
				fitParent:true,
				store,
				columns: [
					column({id:'active',width:40, header:' ', renderer: (v, record) => '<i class="icon">'+(record.lastError ? 'warning' : (v?'check':'close'))+'</i>'}),
					column({id:'name', header: t("Name")}),
					column({id:'lastSync', header: t('Last Sync'),renderer: (date: string) => Format.smartDateTime(date, true)}),
					column({id:'collections', header: t('Collections'), renderer: v => v ? Object.keys(v).length+'' : '0'}),
					column({
						sticky: true,
						width: 32,
						id: "btn",
						renderer: (columnValue: any, record, td, table, rowIndex) => {

							return btn({
								icon: "more_vert",
								menu: menu({},
									btn({icon: 'sync', text:'Sync', handler:(me) => {
											const acc = table.store.get(rowIndex)!;

											me.disabled = true;
											this.mask();
											client.jmap('DavAccount/sync', {accountId:acc.id}).then((response)=> {
												store.reload();
											}).catch((err) => {
												me.disabled = false;
												Window.error(err);
											}).finally(() => {
												this.unmask();
											})
										}
									}),

									btn({
										icon: "edit",
										text: t("Edit"),
										handler: async (btn) => {
											const acc = table.store.get(rowIndex)!;
											const d = new AccountWindow();
											await d.load(acc.id);
											d.show();
										}
									}),
									hr(),
									btn({
										icon: "delete",
										text: t("Delete"),
										handler: async (btn) => {
											const acc = table.store.get(rowIndex)!;
											this.deleteAccounts([acc.id]);
										}
									})

								)
							})
						}
					}),
				],
				rowSelectionConfig: {
					multiSelect: false,
				},
				listeners: {
					rowdblclick:( {target, storeIndex}) => {
						const d = new AccountWindow();
						d.show();
						void d.load(target.store.get(storeIndex)!.id!);
					},

					delete: async ({target}) => {
						const ids: string[] = target.rowSelection!.getSelected().map(row => row.id);
						this.deleteAccounts(ids);
					},
					render: ({target}) => { void target.store.load(); }
				}
			})
		));
	}

	private deleteAccounts(ids:EntityID[]) {
		// ask to keep data
		const w = win({
				modal: true,
				title: t('Keep calendar data?'),
				closable: false,
				width: 600,
				listeners: {focus: ({target}) => {
						target.findChild("yes")!.focus();
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
	}
}