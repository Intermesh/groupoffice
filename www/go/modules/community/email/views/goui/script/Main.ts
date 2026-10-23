import {
	btn, CardContainer, cards, comp,
	Component, DefaultEntity, hr, List, menu, router, splitter, tbar, t
} from "@intermesh/goui";
import {AccountList} from "./AccountList";
import {ListView} from "./ListView";
import {ThreadView} from "./ThreadView";
import {MailCtlr} from "./MailCtlr";
import {Composer} from "./Composer";
import {client, jmapds} from "@intermesh/groupoffice-core";


export class Main extends Component {

	west: Component
	center: Component
	cards: CardContainer
	currentText: Component

	private currentMailbox?: string;
	/** Increased on every navigation so a slow response can't show a thread that is no longer selected */
	private navId = 0;
	private currentAccount?: string

	private listView: ListView
	private threadView: ThreadView

	/**
	 * @param item 't' + thread id for a conversation or 'e' + email id for a single email
	 */
	private routeTo(account:string, mailbox?: string, item?: string) {
		let path = 'a'+account;
		if(mailbox) {
			path += '/m'+mailbox;
			if(item) {
				path += '/'+item;
			}
		}

		router.goto("email/" + path);
	}


	goto(accountId:string, mailboxId: string, threadId?:string, emailId?: string) {
		this.currentAccount = accountId;
		if(this.currentMailbox !== mailboxId) {
			this.currentMailbox = mailboxId;
			this.listView.goto(accountId, mailboxId);
		}
		const navId = ++this.navId;
		// on small screens only the active panel is visible (tablet-cards)
		this.activatePanel(threadId || emailId ? this.threadView : this.center);
		if(emailId) {
			this.loadEmails(accountId, [emailId], navId);
		} else if(!threadId) {
			this.threadView.clear();
		} else {
			jmapds('Thread').single(threadId).then(thread => {
				if(navId !== this.navId) {
					return;
				}
				if(!thread) {
					// threads are destroyed when merged into another conversation
					this.showList();
					return;
				}
				this.loadEmails(accountId, thread.emailIds as string[], navId);
			}).catch(() => {
				if(navId === this.navId) {
					this.showList();
				}
			});
		}
	}

	private loadEmails(accountId: string, ids: string[], navId: number) {
				const ds = jmapds('Email');
				client.jmap('Email/get', {
					accountId: accountId,
					ids,
					properties: ["threadId","accountId", "mailboxIds", "keywords", "from", "subject","to","cc","bcc","replyTo","receivedAt","sentAt","preview",
						"messageId","inReplyTo","references", "htmlBody", "attachments", "bodyStructure", "bodyValues"],
					bodyProperties: ["partId", "blobId", "size", "type","disposition","name"],
					//fetchHTMLBodyValues: true,
					maxBodyValueBytes: 256 // todo (truncate?)
				}).then( (r) => {
					r.list.forEach((e: DefaultEntity) => {
						// add() is protected. but there is no other way to save this to the entity store yet.
						/** @ts-ignore */
						ds.add(Object.assign(ds.data[e.id]??{},e));
					});
					if(navId === this.navId) {
						this.threadView.load(ids);
					}
				}).catch(() => {
					if(navId === this.navId) {
						this.showList();
					}
				});
	}

	/**
	 * Clear the thread view and show the list, so small screens aren't left on an empty thread view without a back button
	 */
	private showList() {
		this.threadView.clear();
		this.activatePanel(this.center);
	}

	/**
	 * Show the given panel. Only has effect on small screens where the tablet-cards layout shows one panel at a time.
	 */
	activatePanel(active: Component) {
		active.parent?.items.forEach(c => c.el.classList.remove("active"));
		active.el.classList.add("active");
	}

	constructor() {
		super();
		this.cls = 'hbox fit tablet-cards';
		this.threadView = new ThreadView();
		this.listView = new ListView();
		this.threadView.on('close', () => {
			if(this.currentAccount) {
				this.routeTo(this.currentAccount, this.currentMailbox);
			}
		});
		this.threadView.on('searchfrom', ({email}) => {
			// the results show in the list, which is hidden behind the thread on small screens
			if(this.currentAccount) {
				this.routeTo(this.currentAccount, this.currentMailbox);
			}
			this.listView.searchFrom(email);
		});
		const //tableView= new TableView(),
			accountList = new AccountList()

		// tableView.on('selectmails', (me, day) => {
		// 	this.routeTo('email', day);
		// });
		this.listView.on('selectmail', ({row}) => {
			this.routeTo(row.record.accountId, this.currentMailbox,
				MailCtlr.conversations() ? 't' + row.record.threadId : 'e' + row.record.id);
		});
		accountList.on('selectmailbox', ({account,mailbox}) => {
			this.routeTo(account.id,mailbox.id);
			// the route doesn't change when the current mailbox is selected again
			this.activatePanel(this.center);
		})

		this.items.add(
			this.west = this.westPanel(accountList),
			splitter({
				stateId: "email-splitter-west",
				resizeComponent: this.west
			}),
			this.center = comp({cls: 'vbox active' ,width: 400},
				tbar({},
					btn({cls: "for-medium-device", icon: "menu", title: t("Show sidebar"),
						handler: () => this.activatePanel(this.west)
					}),
					this.currentText = comp({tagName: 'h3', cls: 'mailbox-title', text: t('Inbox'), flex: '1'}),
					btn({icon:'refresh',handler: () => {
						client.jmap('EmailAccount/update',{accountId:this.currentAccount,mailboxId:this.currentMailbox}).then(r => {
							this.listView.store.reload();
						})
					}}),
					btn({icon: 'search', title: t('Search')}),
					btn({icon:'more_vert',cls: 'not-small-device', menu:menu({},
						btn({icon:'move',text:t('Move to')+'…', handler: _ => { }}),
						btn({icon:'fiber_manual_record',text:t('Mark unread')+'…', handler: _ => { }}),
						btn({icon:'flag',text:t('Flag')+'…', handler: _ => { }}),
						hr(),
						btn({icon:'move',text:t('Forward as attachment')+'…', handler: _ => { }}),
						hr(),
						btn({icon:'report', text:t('Report spam')+'…', handler: _ => { }})
					)}),
					btn({icon: 'edit', text: t('Compose'), cls: 'filled primary',
						handler: _ => {
							const c = new Composer();
							c.show();
						}
					})
				),
				this.cards = cards({flex: 1, activeItem:0, cls:'scroll bg-lowest'},
					//tableView,
					this.listView
				)
			),
			splitter({
				stateId: "email-splitter-east",
				resizeComponent: this.center
			}),
			this.threadView
		);

		//this.on('render', () => { inviteStore.load(); });
	}

	private westPanel(accountList: AccountList) {

		return comp({tagName: 'aside', width: 274, cls:'scroll',style: {minWidth: '27.4rem'}},
			tbar({cls: "for-medium-device"},
				'->',
				btn({title: t("Back"), icon: "arrow_back",
					handler: () => {
						this.activatePanel(this.center);
					}
				})
			),

			comp({cls:'scroll'},
				accountList
			)
		)
	}

}