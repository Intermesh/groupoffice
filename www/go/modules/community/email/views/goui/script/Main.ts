import {
	btn, CardContainer, cards, comp,
	Component, hr, List, menu, router, splitter, tbar, t
} from "@intermesh/goui";
import {AccountList} from "./AccountList";
import {ListView} from "./ListView";
import {ThreadView} from "./ThreadView";
import {MailCtlr} from "./MailCtlr";
import {Composer} from "./Composer";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {QuotaBar} from "./QuotaBar";
import {accountStore} from "./Index";


export class Main extends Component {

	west: Component
	center: Component
	cards: CardContainer
	currentText: Component

	private currentMailbox?: string;
	/** Increased on every navigation so a slow response can't show a thread that is no longer selected */
	private navId = 0;
	private currentAccount?: string
	/** The conversation or email in the route, highlighted in the list whenever it loads */
	private currentThread?: string;
	private currentEmail?: string;

	private listView: ListView
	private quotaBar = new QuotaBar()
	private threadView: ThreadView

	/**
	 * @param item 't' + thread id for a conversation or 'e' + email id for a single email
	 * @param replace replace the current history entry, so Back doesn't return to a route that redirects again
	 */
	private routeTo(account:string, mailbox?: string, item?: string, replace = false) {
		let path = 'a'+account;
		if(mailbox) {
			path += '/m'+mailbox;
			if(item) {
				path += '/'+item;
			}
		}

		if(replace) {
			location.replace("#email/" + path);
		} else {
			router.goto("email/" + path);
		}
	}


	goto(accountId:string, mailboxId: string, threadId?:string, emailId?: string) {
		if(this.currentAccount !== accountId) {
			void this.quotaBar.load(accountId);
		}
		this.currentAccount = accountId;
		if(this.currentMailbox !== mailboxId) {
			this.currentMailbox = mailboxId;
			void this.listView.goto(accountId, mailboxId);
		}
		const navId = ++this.navId;
		this.currentThread = threadId;
		this.currentEmail = emailId;
		this.listView.selectMail(threadId, emailId);
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

	/**
	 * Show the inbox of the given account, or of the first account when none is given
	 */
	async openAccount(accountId?: string) {
		accountId ??= accountStore.get(0)?.id;
		if(!accountId) {
			return;
		}
		const path = router.getPath();
		const inbox = await jmapds('Mailbox').query({accountId, filter: {role: 'inbox'}} as any);
		// don't override a navigation that happened while looking up the inbox
		if(inbox.ids.length && router.getPath() === path) {
			this.routeTo(accountId, inbox.ids[0] + '', undefined, true);
		}
	}

	private loadEmails(accountId: string, ids: string[], navId: number) {
		MailCtlr.getWithBody(accountId, ids).then(list => {
			if(navId === this.navId) {
				this.threadView.load(list);
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
		// also after a reload, where a newer reply can become the row of the open conversation
		this.listView.store.on('load', () => {
			this.listView.selectMail(this.currentThread, this.currentEmail);
		});
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
						if(!this.currentAccount) {
							return;
						}
						client.jmap('EmailAccount/update',{accountId:this.currentAccount,mailboxId:this.currentMailbox}).then(r => {
							this.listView.store.reload();
							void this.quotaBar.load(this.currentAccount!);
						})
					}}),
					btn({icon: 'search', title: t('Search')}),
					btn({icon: 'edit', text: t('Compose'), cls: 'filled primary',
						handler: _ => {
							const c = new Composer();
							c.show();
						}
					}),
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

		this.on('render', () => {
			// opened as plain #email, without an account and mailbox in the route
			if(!this.currentAccount && /^email\/?$/.test(router.getPath())) {
				// the route then loads the quota of the opened account
				void this.openAccount();
			} else {
				// before a mailbox is opened show the usage of the first account
				const first = accountStore.data[0];
				if(!this.currentAccount && first) {
					void this.quotaBar.load(first.id);
				}
			}
		});
	}

	private westPanel(accountList: AccountList) {

		// one header on small screens: the back button goes into the account list's own toolbar
		accountList.toolbar.items.insert(0, btn({cls: "for-medium-device", title: t("Back"), icon: "arrow_back",
			handler: () => {
				this.activatePanel(this.center);
			}
		}));

		return comp({tagName: 'aside', width: 274, cls:'vbox',style: {minWidth: '27.4rem'}},
			comp({cls:'scroll', flex: 1},
				accountList
			),
			this.quotaBar
		)
	}

}