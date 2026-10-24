import {
	btn,
	comp,
	datasourcestore,
	hr,
	List,
	menu,
	t,
	E,
	DateTime,
	Format,
	ListEventMap, Store, StoreRecord
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {fileView} from "./FileView";
import {MailCtlr} from "./MailCtlr";
import {ThreadView} from "./ThreadView";

/**
 * Time for mail received today, the date otherwise
 */
const receivedText = function(receivedAt: string) {
	return Format.date(receivedAt) === Format.date(new DateTime()) ? Format.time(receivedAt) : Format.date(receivedAt);
}

const listitem = function(mail: any) {
	const abbr =  comp({tagName:'abbr'}),
		text = comp({tagName:'div', cls:'line'});
	abbr.el.append(
		!mail.keywords?.$seen ?  E('b').cls('dot'):'',
		mail.keywords?.$answered ? E('i', 'reply').cls('small icon').css({color:'green'}):'',
		mail.keywords?.$forwarded ? E('i', 'forward').cls('small icon').css({color:'purple'}):'',
		mail.keywords?.$flagged ? E('i', 'flag').cls('small icon').css({color:'red'}):'',
		E('br'),
		mail.hasAttachment ? E('i', 'attachment').cls('small icon'):''
	);
	text.el.append(
		E('h4', receivedText(mail.receivedAt)).cls('right'),
		E('h3', mail.from.length ? (mail.from[0].name || mail.from[0].email) : ''),
		E('div', mail.subject || '('+t('No subject')+')',
			MailCtlr.conversations() && mail.thread?.emailIds?.length > 1 ? E('div', String(mail.thread.emailIds.length)).cls('badge primary') :''
		).cls('subject'),
		E('sub', mail.preview).cls('clamp')
	);
	return [abbr,text];
}

export interface ListViewEventMap extends ListEventMap {
	selectmail: {row: any}
}

export class ListView extends List<Store, ListViewEventMap> {

	/** Set while the row of the opened mail is highlighted, which must not navigate again */
	private selectingSilently = false;

	constructor() {
		super(
			datasourcestore({
				dataSource:jmapds('Email'),
				relations: {
					thread: {
						dataSource: jmapds("Thread"),
						path: "threadId"
					}
				},
				sort:[{property:'receivedAt',isAscending:false}]}),
			function (record,row,list,storeIndex) {
				return listitem(record);
			}
		);
		this.cls = 'list email-list';
		this.rowSelectionConfig = {
			multiSelect:false,
			listeners: {
				'rowselect': ({row})=> {
					if(!this.selectingSilently) {
						this.fire('selectmail', {row});
					}
				}
			}
		};

		let clickedItem: StoreRecord | undefined;

		const rowContextMenu = menu({isDropdown: true, removeOnClose: false},
			btn({icon: 'send', text: t('Send again'), handler: (btn) => { MailCtlr.resend(clickedItem).show(); }}),
			btn({icon: 'reply', text: t('Reply'), handler: (btn) => { MailCtlr.reply(clickedItem).show(); }}),
			btn({icon: 'reply_all', text: t('Reply all'), handler: (btn) => { MailCtlr.reply(clickedItem,false).show();}}),
			btn({icon: 'forward', text: t('Forward'), handler: (btn) => { MailCtlr.forward(clickedItem).show();}}),
			hr(),
			btn({icon: 'mail', text: t('Mark as unread'), handler:() => { MailCtlr.flag(this.rowSelection!.getSelected(), '$seen', null)}}),
			btn({icon: 'report', text: t('Move to "Spam"'), disabled: true}),
			btn({icon: 'delete', text: t('Delete'), disabled: true}),
			hr(),
			btn({icon: 'flag', text: t('Flag'), handler:() => { MailCtlr.flag(this.rowSelection!.getSelected(), '$flagged', null)}}),
			hr(),
			btn({icon: 'archive', text: t('Archive'), disabled: true}),
			btn({icon: 'folder_open', text: t('Move')+'...', disabled: true}),
			btn({icon: 'folder_open', text: t('Copy')+'...', disabled: true}),
			hr(),
			btn({icon: 'code', text: t('View source'), handler: () => {
				fileView.open({blobId:'community/email/src/'+clickedItem!.id, type: 'text/plain', name: 'mailsrc.eml'}).show();
			}})
		);

		this.on('rowcontextmenu',({ev, storeIndex}) =>{
			ev.preventDefault();
			clickedItem = this.store.get(storeIndex);
			rowContextMenu.showAt(ev);
		})

	}


	goto(accountId:string, mailboxId:string) {
		this.store.queryParams.accountId = accountId;
		const filter = this.store.queryParams.filter ||= {};
		delete filter.from;
		Object.assign(filter, {mailboxId});
		// one row per conversation (RFC 8621 Email/query collapseThreads)
		(this.store.queryParams as any).collapseThreads = MailCtlr.conversations();
		return this.store!.load();
	}

	/**
	 * Highlight the row of the given conversation or email, e.g. when it was opened by a link
	 */
	selectMail(threadId?: string, emailId?: string) {
		const index = this.store.findIndex(r => emailId ? r.id == emailId : r.threadId == threadId);
		if(index === -1) {
			this.rowSelection!.clear();
			return;
		}
		this.selectingSilently = true;
		try {
			this.rowSelection!.add(this.store.get(index)!);
		} finally {
			this.selectingSilently = false;
		}
	}

	/**
	 * Show only mail from this sender in the current mailbox
	 */
	searchFrom(email: string) {
		Object.assign(this.store.queryParams.filter ||= {}, {from: email});
		return this.store!.load();
	}
}