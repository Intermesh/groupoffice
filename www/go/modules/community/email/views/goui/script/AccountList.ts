import {
	btn,
	comp,
	Component,
	hr,
	list,
	List,
	menu,
	tbar,
	t,
	tree,
	treecolumn,
	TreeRecord, ComponentEventMap, DateTime, ObservableListenerOpts
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {AccountWindow} from "./AccountWindow";
import {accountStore} from "@intermesh/community/email";
import {IdentityWindow} from "./IdentityWindow";
import {SettingsWindow} from "./SettingsWindow";

export interface AccountListEventMap<Type> extends ComponentEventMap<Type> {
	selectaccount: (me: Type, account: any) => false | void
	selectmailbox: (me: Type, account: any, mailbox:any) => void
}

export interface AccountList extends Component {
	on<K extends keyof AccountListEventMap<this>, L extends Function>(eventName: K, listener: Partial<AccountListEventMap<this>>[K], options?: ObservableListenerOpts): L
	fire<K extends keyof AccountListEventMap<this>>(eventName: K, ...args: Parameters<AccountListEventMap<any>[K]>): boolean
}

export class AccountList extends Component {

	list:List

	constructor(){
		super()

		const mailboxDS = jmapds('Mailbox');

		const identityDialog = new IdentityWindow(),
			settingsDialog = new SettingsWindow(),
			accountMenu = menu({cls:'dropdown'},

			);

		this.items.add(tbar({cls: 'dense'},
			comp({tagName: 'h3', html: t('Accounts')}),
			btn({
				icon: 'more_vert', menu: menu({},
					btn({
						icon: 'add',
						text: t('Add account') + '…', handler: () => {
							const dlg = new AccountWindow();
							dlg.form.create({});
							dlg.show();
						}
					}),
					btn({
						icon: 'bookmark_added',
						text: t('Subscribe to mailbox') + '…', handler: () => {
							// const d = new SubscribeWindow();
							// d.show();
						}
					})
				)
			})
		), this.list = list({
			tagName: 'div',
			store:accountStore,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': (tableRowSelect) => {
						//todo
					}
				}
			},
			listeners: {'render': me => {
					me.store.load();
				}},
			renderer: (account: any, _row: HTMLElement, _list: List, _storeIndex: number) => {
				// if(data.isVisible) {
				// 	this.inCalendars[storeIndex] = true;
				// }
				return [btn({
					icon: account.role,
					//style: 'padding: 0 8px',
					text: account.name,
					menu: menu({},
						btn({icon: 'settings',text: t('Settings'), handler: function() {settingsDialog.show().form.load('mail');}}), // hidden: !$.auth.roles.admin,
						btn({icon: 'badge', 	text: t('Identities'), handler: function() {identityDialog.show();}}),
						btn({icon: 'refresh', text: 'Refetch all', handler: () => { this.imapFill(account.id /*aid*/) }}),
						'-',
						btn({icon:'edit', text: t('Edit')+'…', disabled:!account.myRights.mayAdmin, handler: async _ => {
								const dlg = new AccountWindow();
								await dlg.load(account.id);
								dlg.show();
							}}),
						btn({icon:'delete', text: t('Delete','core','core')+'…', disabled:!account.myRights.mayAdmin, handler: async _ => {
								jmapds("Mailbox").confirmDestroy([account.id]);
							}}),
						hr(),
						btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
								jmapds('Mailbox').update(account.id, {isSubscribed: false});
							}}),
						hr(),
						btn({icon:'file_save',hidden:account.groupId, text: t('Share','core','core'), handler: _ => {  }}),

					)

				}),tree({
					fitParent:true,
					columns: [
						treecolumn({
							id: "name",
							header: "Name",
							defaultIcon: "folder",
							sortable: true
						})
					],
					rowSelectionConfig: {
						multiSelect:false,
						listeners:{
							'rowselect':(me, mailboxRow)=> {

								this.fire('selectmailbox',this, account, mailboxRow);
							}
						}
					},
					nodeProvider: async (record, store) : Promise<TreeRecord[]> => {

						let childIds;
						if(record) {
							// We already fetched the childIds in its parent. See below
							childIds = record.childIds;
						} else {
							// When there's no record we're fetching the root of the tree
							const q = await mailboxDS.query({
								filter: {accountId: account.id, parentId: null},
								//sort: store.sort
							});

							childIds = q.ids;
						}

						const getResponse = await mailboxDS.get(childIds)
						//at the root of the tree record is undefined
						return Promise.all(getResponse.list.map(async (e) => {
							// prefetch child id's so the Tree component knows if this node has children
							const childIds = (await mailboxDS.query({filter: {accountId: account.id, parentId: e.id}})).ids;

							return {
								id: e.id + "",
								name: e.name,
								createdAt: e.createdAt,

								// Store the child id's in the node record so we can use it when it's expanded
								childIds,

								// Set to empty array if it has no children. Then the Tree component knows it's a leaf and won't present an expand arrow
								children: childIds.length ? undefined : []
							}
						}))
					},
				})];
			}
		}));
	}

	private imapFill(accountId: number) {
		client.jmap('EmailAccount/fill',{accountId}).then(r => {
			alert('done');
		})
	}

}