import {
	btn, CardContainer, cards, comp,
	Component, DefaultEntity, hr, List, menu, router, splitter, tbar
} from "@intermesh/goui";
import {CalendarList, calendarStore, t} from "@intermesh/community/calendar";
import {AccountList} from "./AccountList";
import {ListView} from "./ListView";
import {IdentityWindow} from "./IdentityWindow";
import {SettingsWindow} from "./SettingsWindow";
import {ThreadView} from "./ThreadView";
import {MailCtlr} from "./MailCtlr";
import {Composer} from "./Composer";
import {client, jmapds} from "@intermesh/groupoffice-core";


export class Main extends Component {

	west: Component
	cards: CardContainer
	quotaPanel!:Component
	currentText: Component

	private currentMailbox?: string;

	private listView: ListView
	private threadView: ThreadView

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


	goto(accountId:string, mailboxId: string, threadId?:string) {
		if(this.currentMailbox !== mailboxId) {
			this.currentMailbox = mailboxId;
			this.listView.goto(accountId, mailboxId);
		}
		if(threadId) {
			jmapds('Thread').single(threadId).then(thread => {
				const ds = jmapds('Email');
				client.jmap('Email/get', {
					ids: thread!.emailIds,
					properties: ["threadId","accountId", "mailboxIds", "from", "subject","to","cc","bcc","receivedAt", "htmlBody", "attachments", "bodyStructure", "bodyValues"],
					bodyProperties: ["partId", "blobId", "size", "type","disposition","name"],
					//fetchHTMLBodyValues: true,
					maxBodyValueBytes: 256 // todo (truncate?)
				}).then( (r) => {
					r.list.forEach((e: DefaultEntity) => {
						// add() is protected. but there is no other way to save this to the entity store yet.
						/** @ts-ignore */
						ds.add(Object.assign(ds.data[e.id]??{},e));
					});
					this.threadView.load(thread);
				});
			});
		}
	}

	constructor() {
		super();
		this.cls = 'hbox fit tablet-cards';
		this.threadView = new ThreadView();
		this.listView = new ListView();
		const //tableView= new TableView(),
			accountList = new AccountList()

		// tableView.on('selectmails', (me, day) => {
		// 	this.routeTo('email', day);
		// });
		this.listView.on('selectmail', (me,row) => {
			this.routeTo(row.record.accountId, this.currentMailbox, row.record.threadId);
		});
		accountList.on('selectmailbox', (me,account, mailbox) => {
			this.routeTo(account.id,mailbox.id);
		})

		let center;

		this.items.add(
			this.west = this.westPanel(accountList),
			splitter({
				stateId: "email-splitter-west",
				resizeComponentPredicate: this.west
			}),
			center=comp({cls: 'vbox active' ,width: 400},
				tbar({},
					btn({icon: 'add', text: t('Compose'),cls:'primary filled',
						handler: _ => {
							const c = new Composer();
							c.show();
						}
					}),
					this.currentText = comp({tagName: 'h3', text: t('Inbox'), flex: '1 1 50%', style: {minWidth: '100px', fontSize: '1.8em'}}),
					'->',
					btn({icon: 'search', title: t('Search')}),
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
			),
			splitter({
				stateId: "email-splitter-east",
				resizeComponentPredicate: center
			}),
			this.threadView
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