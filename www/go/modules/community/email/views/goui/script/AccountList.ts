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
	TreeRecord, ComponentEventMap, Tree
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {AccountWindow} from "./AccountWindow";
import {IdentityWindow} from "./IdentityWindow";
import {SettingsWindow} from "./SettingsWindow";
import {accountStore} from "./Index";

export interface AccountListEventMap extends ComponentEventMap {
	selectaccount: { account: any}
	selectmailbox: {account: any, mailbox:any}
}

export class AccountList extends Component<AccountListEventMap> {

	list:List


	private static mailboxRoles = { // key: label, icon, color
		inbox: [t('Inbox','mail'), 'inbox'],
		drafts: [t('Drafts','mail'), 'drafts'],
		sent: [t('Sent','mail'), 'send'],
		trash: [t('Trash','mail'), 'delete'],
		spam: [t('Junk','mail'), 'delete_forever'],
		archive: [t('Archive','mail'), 'archive'],
	}

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
			store: accountStore,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': (tableRowSelect) => {
						//todo
					}
				}
			},
			listeners: {'render': ({target}) => {
					target.store.load();
				}},
			renderer: (account: any, _row: HTMLElement, _list: List, _storeIndex: number) => {
				// if(data.isVisible) {
				// 	this.inCalendars[storeIndex] = true;
				// }
				const mboxTree = tree({
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
							'rowselect':({row})=> {

								this.fire('selectmailbox',{account, mailbox:row});
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
							/** @ts-ignore */
							const r: any[]|undefined = AccountList.mailboxRoles[e.role];
							return {
								id: e.id + "",
								name: r ? r[0] : e.name,
								icon: r ? r[1] : 'folder',
								createdAt: e.createdAt,

								// Store the child id's in the node record so we can use it when it's expanded
								childIds,

								// Set to empty array if it has no children. Then the Tree component knows it's a leaf and won't present an expand arrow
								children: childIds.length ? undefined : []
							}
						}))
					},
				});

				return [btn({
					icon: 'account_box',
					//style: 'padding: 0 8px',
					text: account.name,
					menu: menu({},
						btn({icon: 'settings',text: t('Settings'), handler: function() {settingsDialog.show(); }}), // hidden: !$.auth.roles.admin,
						btn({icon: 'badge', 	text: t('Identities'), handler: function() {identityDialog.show();}}),
						btn({icon: 'refresh', text: 'Refetch all', handler: () => { this.imapFill(account.id, mboxTree) }}),
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

				}),mboxTree];
			}
		}));
	}

	private imapFill(accountId: number, mboxTree: Tree) {
		client.jmap('EmailAccount/fill',{accountId}).then(r => {
			mboxTree.store.reload();
		})
	}

}