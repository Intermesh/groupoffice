import {
	t,
	textfield,
	select,
	comp,
	containerfield,
	fieldset,
	btn,
	tbar,
	list,
	datasourcestore,
	E, Component, numberfield, checkbox, store, column, table, menu
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {IdentityWindow} from "./IdentityWindow";
import {viewStore} from "@intermesh/community/calendar";

export class AccountWindow extends FormWindow {

	identitiesPanel: Component
	constructor() {
		super('EmailAccount');
		this.title = t('Account');
		this.height = 800;
		this.width = 500;

		this.generalTab.items.add(
			fieldset({},
				textfield({label: t('Name'), name: 'name'}),
				textfield({label: t('E-Mail'), name: 'email'})
			),
			//mda
			fieldset({legend:t('Incoming')},
				containerfield({name:'mda'},
					textfield({label: t('Hostname'), flex:'.8',name: 'host'}),
					numberfield({label: t('Port'),flex:'.2',decimals:0,min:1, placeholder:'143', name: 'port'}),
					textfield({label: t('Username'), name: 'user'}),
					textfield({label: t('Password'), name: 'pass', type: 'password'}),
					select({width: 140,label: 'Security', name: 'encryption', options:[
						{name:t('SSL'), value: 'ssl'},
						{name:t('Start/TLS'), value: 'tls'},
						{name:t('Plain'), value: 'none'},
					]}),
					checkbox({flex:'1 1 0',label: t('Allow self-signed certificate'), name:'selfSigned'})
				)
			),
			fieldset({legend:t('Outgoing')},
				containerfield({name:'mta'},
					textfield({label: t('Hostname'), flex:'.8', name: 'host'}),
					numberfield({label: t('Port'),flex:'.2', decimals:0,min:1, placeholder:'25', name: 'port'}),
					textfield({label: t('Username'), name: 'user'}),
					textfield({label: t('Password'), name: 'pass', type: 'password'}),
					select({width: 140,label: 'Security', name: 'encryption', options:[
						{name:t('SSL'), value: 'ssl'},
						{name:t('Start/TLS'), value: 'tls'},
						{name:t('Plain'), value: 'none'},
					]}),
					checkbox({flex:'1 1 0',label: t('Allow self-signed certificate'), name:'selfSigned'})
				)
			)
		);
		// list that hold the accounts, each item having one or more identities

		const f= this.form,
			idStore = store({
			async onLoad(store) {
				const get = await jmapds("Identity").getAll(f.currentId!);
				get.list.forEach((identity: any) => {
					store.add(identity);
				})
			}
		});

		const idTable = table({
			store: idStore,
			emptyStateHtml:'',
			fitParent: true,
			headers:false,
			columns:[
				column({id:'name'}),
				column({id:'from'}),
				column({ width: 50,
				id: '-', renderer: (v, data) => btn({
					//hidden: !rights.mayChangeViews,
					icon: 'more_horiz', menu: menu({},
						btn({icon: 'edit', text: t('Edit'), handler: async _ => {
								const dlg = new IdentityWindow(f.currentId!);
								await dlg.load(data.id);
								dlg.show();
							}
						}),
						btn({icon: 'delete', text: t('Delete'), handler: async _ => {
								idStore.dataSource.confirmDestroy([data.id]);
							}
						})
					)
				})
			})]
		});

		this.identitiesPanel = comp({title: t('Identities')},
			tbar({},
				comp({text:t('Identities')}),'->',
				btn({title: t('Add Identity'),cls:'small', icon: 'add'}).on('click',b => {
					if(!this.form.currentId) {
						alert('Create the account first');
						return;
					}
					const idw = new IdentityWindow(this.form.currentId!);
					idw.show();
				}),
			),
			idTable.on('rowdblclick', () => {})
		).on('render', ({target}) => {
			if(this.form.currentId) {
				jmapds('Identity').getParams.accountId = this.form.currentId;
				idTable.store.load();
			}
		});


		this.cards.items.add(this.identitiesPanel);


		this.addSharePanel();
	}
}