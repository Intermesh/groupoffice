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
	ObservableListenerOpts, ListEventMap
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {MailCtlr} from "./MailCtlr";
import {ThreadView} from "./ThreadView";

const listitem = function(mail) {
	const abbr =  comp({tagName:'abbr'}),
		text = comp({tagName:'div', cls:'line'});
	const tr = {emailIds:[]}; //fake thread
	abbr.el.append(
		!mail.keywords?.$seen ?  E('b', '•').cls('large'):'',
		mail.keywords?.$answered ? E('i', 'reply').cls('small icon').css({color:'green'}):'',
		mail.keywords?.$forwarded ? E('i', 'forward').cls('small icon').css({color:'purple'}):'',
		mail.keywords?.$flagged ? E('i', 'flag').cls('small icon').css({color:'red'}):'',
		E('br'),
		mail.hasAttachment ? E('i', 'attachment').cls('small icon'):''
	);
	text.el.append(
		E('h4', DateTime.createFromFormat(mail.receivedAt)!.format('d-m-Y')).cls('right').css({float:'right'}),
		E('h3', mail.from.length ? (mail.from[0].name || mail.from[0].email) : ''),
		E('div', mail.subject || '('+t('No subject')+')',
			tr.emailIds.length > 1 ? E('div', tr.emailIds.length+' »').cls('badge primary right') :''
		).cls('subject'),
		E('sub', mail.preview).cls('clamp')
	);
	return [abbr,text];
}

export interface ListViewEventMap<Type> extends ListEventMap<Type> {
	selectmail: (me: Type, row: any) => false | void
}

export interface ListView extends List {
	on<K extends keyof ListViewEventMap<this>, L extends Function>(eventName: K, listener: Partial<ListViewEventMap<this>>[K], options?: ObservableListenerOpts): L
	fire<K extends keyof ListViewEventMap<this>>(eventName: K, ...args: Parameters<ListViewEventMap<any>[K]>): boolean
}

export class ListView extends List {

	constructor() {
		super(
			datasourcestore({dataSource:jmapds('Email'), sort:[{property:'receivedAt',isAscending:false}]}),
			function (record,row,list,storeIndex) {
				return listitem(record);
			}
		);
		this.cls = 'email-list';
		this.rowSelectionConfig = {
			multiSelect:false,
			listeners: {
				'rowselect':(me,row)=> {
					this.fire('selectmail', this, row);
				}
			}
		};

		let clickedItem = null;

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
			btn({icon: 'code', text: t('View source'), handler: () => {window.open(client.downloadBlobId('mail.src.todo'))}})
		);

		this.on('rowcontextmenu',(_me, index, row, ev) =>{
			ev.preventDefault();
			clickedItem = this.store.get(index);
			rowContextMenu.showAt(ev);
		})

	}


	goto(accountId:string, mailboxId:string) {
		Object.assign(this.store.queryParams.filter ||= {}, {
			accountId,
			mailboxId
		});
		return this.store!.load();
	}
}