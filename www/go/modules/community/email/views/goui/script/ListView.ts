import {btn, comp, Component, datasourcestore, hr, List, menu, t, E, treecolumn, DateTime} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {MailCtlr} from "./MailCtlr";

const listitem = function(mail) {
	const item =  comp({tagName:'li'});
	const tr = {emailIds:[]}; //fake thread
	item.el.append(E('abbr',
		!mail.keywords?.$seen ?  E('b', '•').cls('large'):'',
		mail.keywords?.$answered ? E('i', 'reply').cls('small').css({color:'green'}):'',
		mail.keywords?.$forwarded ? E('i', 'forward').cls('small').css({color:'purple'}):'',
		mail.keywords?.$flagged ? E('i', 'flag').cls('small').css({color:'red'}):'',
		E('br'),
		mail.hasAttachment ? 'P!':''
	),
	E('div',
		E('h3', mail.from ? mail.from[0].name || mail.from[0].email : ''),
		E('h4', DateTime.createFromFormat(mail.receivedAt)!.format('d-m-Y')).cls('right').css({float:'right'}),
		E('div', mail.subject || '('+t('No subject')+')',
			tr.emailIds.length > 1 ? E('div', tr.emailIds.length+' »').cls('badge primary right') :''
		).cls('subject'),
		E('sub', mail.preview).cls('clamp')
	).cls('line'));
	return item;
}

export class ListView extends List {

	constructor() {
		super(
			datasourcestore({dataSource:jmapds('Email'), sort:[{property:'receivedAt',isAscending:false}]}),
			function (record,row,list,storeIndex) {
				return [listitem(record)];
			}
		);

		const rowContextMenu = menu({},
			btn({icon: 'send', text: t('Send again'), handler: (btn) => { MailCtlr.resend(this.lastId).show(btn.dom); }}),
			btn({icon: 'reply', text: t('Reply'), handler: (btn) => { MailCtlr.reply(this.lastId).show(btn.dom); }}),
			btn({icon: 'reply_all', text: t('Reply all'), handler: (btn) => { MailCtlr.reply(this.lastId,false).show(btn.dom);}}),
			btn({icon: 'forward', text: t('Forward'), handler: (btn) => { MailCtlr.forward(this.lastId).show(btn.dom);}}),
			hr(),
			btn({icon: 'mail', text: t('Mark as unread'), handler:() => { MailCtlr.flag(this.list.selectedIds, '$seen', null)}}),
			btn({icon: 'report', text: t('Move to "Spam"'), disabled: true}),
			btn({icon: 'delete', text: t('Delete'), disabled: true}),
			hr(),
			btn({icon: 'flag', text: t('Flag'), handler:() => { MailCtlr.flag(this.list.selectedIds, '$flagged', true)}}),
			hr(),
			btn({icon: 'archive', text: t('Archive'), disabled: true}),
			btn({icon: 'folder_open', text: t('Move')+'...', disabled: true}),
			btn({icon: 'folder_open', text: t('Copy')+'...', disabled: true}),
			hr(),
			btn({icon: 'code', text: t('View source'), handler: () => {window.open(client.downloadBlobId('mail.src.todo'))}})
		);

	}


	goto(accountId:string, mailboxId:string) {
		Object.assign(this.store.queryParams.filter ||= {}, {
			accountId,
			mailboxId
		});
		return this.store!.load();
	}
}