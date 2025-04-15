import {
	btn, CardContainer, cards, comp,
	Component, hr, List, menu, router, splitter, tbar
} from "@intermesh/goui";
import {CalendarList, calendarStore, t} from "@intermesh/community/calendar";
import {AccountList} from "./AccountList";
import {ListView} from "./ListView";
import {IdentityWindow} from "./IdentityWindow";
import {SettingsWindow} from "./SettingsWindow";


export class Main extends Component {

	west: Component
	cards: CardContainer
	quotaPanel!:Component
	currentText: Component

	private listView: ListView

	private routeTo(account:string, mailbox?: string, thread?: string) {
		let path = 'a'+account;
		if(mailbox) {
			path += '/m'+mailbox;
			if(thread) {
				path += '/t'+thread;
			}
		}

		router.goto("email/" + path);
	}


	goto(accountId:string, mailboxId: string) {
		this.listView.goto(accountId,mailboxId);
	}

	constructor() {
		super();
		this.cls = 'hbox fit tablet-cards';
		this.listView = new ListView();
		const //tableView= new TableView(),
			accountList = new AccountList()

		// tableView.on('selectmails', (me, day) => {
		// 	this.routeTo('email', day);
		// });
		this.listView.on('selectmails', (me,day) => {
			//this.routeTo('email', day);
		});
		accountList.on('selectmailbox', (me,account, mailbox) => {
			this.routeTo(account.id,mailbox.id);
		})

		this.items.add(
			this.west = this.westPanel(accountList),
			splitter({
				stateId: "email-splitter-west",
				resizeComponentPredicate: this.west
			}),
			comp({cls: 'vbox active', flex: 1},
				tbar({},
					btn({icon: 'add', title: t('Compose'),
						handler: _ => {}
					}),
					this.currentText = comp({tagName: 'h3', text: t('Inbox'), flex: '1 1 50%', style: {minWidth: '100px', fontSize: '1.8em'}}),
					'->',
					btn({cls:'archive', text:t('Archive')}),
					btn({cls:'delete', text:t('Delete')}),
					btn({cls: 'labels', text: t('Labels')}),
					btn({icon:'more_vert',cls: 'not-small-device', menu:menu({},
						btn({icon:'move',text:t('Move to')+'…', handler: _ => { }}),
						btn({icon:'fiber_manual_record',text:t('Mark unread')+'…', handler: _ => { }}),
						btn({icon:'flag',text:t('Flag')+'…', handler: _ => { }}),
						hr(),
						btn({icon:'move',text:t('Forward as attachment')+'…', handler: _ => { }}),
						hr(),
						btn({icon:'report', text:t('Report spam')+'…', handler: _ => { }})
					)})
				),
				this.cards = cards({flex: 1, activeItem:0, cls:'scroll'},
					//tableView,
					this.listView
				)
			)
		);

		//this.on('render', () => { inviteStore.load(); });
	}

	private westPanel(accountList: AccountList) {

		return comp({tagName: 'aside', width: 274, cls:'scroll',style: {paddingTop:'1.2rem', minWidth: '27.4rem'}},
			tbar({cls: "for-medium-device"},
				'->',
				btn({title: t("Back"), icon: "arrow_back",
					handler: () => {
						this.west.el.cls('-active');
					}
				})
			),

			comp({cls:'scroll'},
				accountList,
				this.quotaPanel = comp({text:'quota'})

			)
		)
	}

}