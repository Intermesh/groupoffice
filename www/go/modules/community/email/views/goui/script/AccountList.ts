import {btn, comp, Component, hr, list, List, menu, tbar, t} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {AccountWindow} from "./AccountWindow";

export class AccountList extends Component {

	list:List

	constructor(store){
		super()

		this.items.add(store !== calendarStore ? comp() :tbar({cls: 'dense'},
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
							const d = new SubscribeWindow();
							d.show();
						}
					}),
					btn({icon: 'travel_explore',text: t('Add calendar from link') + '…'})
				)
			})
		), this.list = list({
			tagName: 'div',
			store,
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
			renderer: this.mailboxRenderer.bind(this)
		}));
	}

	mailboxRenderer(data: any, _row: HTMLElement, _list: List, _storeIndex: number) {
		// if(data.isVisible) {
		// 	this.inCalendars[storeIndex] = true;
		// }
		return [btn({
			icon: data.role,
			//style: 'padding: 0 8px',
			text: data.name,
			menu: menu({},
					btn({icon:'sync', text: t('Synchronize'), hidden: !data.davaccountId, handler: (me) => {

					}}),
					btn({icon:'edit', text: t('Edit')+'…', hidden: data.davaccountId, disabled:!data.myRights.mayAdmin, handler: async _ => {
							const dlg = new MailboxWindow();
							await dlg.load(data.id);
							dlg.show();
						}}),
					btn({icon:'delete', text: t('Delete','core','core')+'…', hidden: data.davaccountId, disabled:!data.myRights.mayAdmin, handler: async _ => {
							jmapds("Mailbox").confirmDestroy([data.id]);
						}}),
					hr(),
					btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
							jmapds('Mailbox').update(data.id, {isSubscribed: false});
						}}),
					hr(),
					btn({icon:'file_save',hidden:data.groupId, text: t('Share','core','core'), handler: _ => {  }}),

				)

		})];
	}
}