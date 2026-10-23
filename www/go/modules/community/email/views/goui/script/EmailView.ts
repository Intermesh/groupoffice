import {
	btn,
	comp,
	Component,
	tbar,
	t,
	E,
	menu,
	Format,
	DataSourceForm,
	avatar, hr, Avatar, Notifier
} from "@intermesh/goui";
import {MailCtlr} from "./MailCtlr";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {fileView} from "./FileView";

export class EmailView extends DataSourceForm {

	/** Set by the conversation view: the close button collapses the message instead of clearing it */
	public onCollapse?: () => void
	/** Set by the conversation view to filter the mail list on a sender */
	public onSearchFrom?: (email: string) => void

	private from: Component
	private avatar: Avatar
	private to: Component
	private received: Component
	private subject: Component
	private body: Component
	private attachments: Component
	private shadowRoot: ShadowRoot

	constructor(inThread = false) {
		super(jmapds('Email'));
		this.cls = 'vbox';
		this.flex = '1';
		this.items.add(
			tbar({},
				inThread ?
					btn({title: t('Collapse'), icon: 'unfold_less', handler: () => { this.onCollapse?.(); } }) :
					btn({title: t('Close'), icon: 'close', handler: () => { this.clear(); } }),
				comp({cls: 'group', flex:'1'},
					btn({icon: 'reply', title: t('Reply'), handler: (target) => { MailCtlr.reply(this.value).show(); } }),
					btn({icon: 'reply_all', title: t('Reply all'), handler: (target) => { MailCtlr.reply(this.value, true).show(); } }),
					btn({title:t('Forward'), icon: 'forward', handler: (target) => { MailCtlr.forward(this.value).show(); } })
				),
				comp({cls: 'group', flex:'1'},
					btn({icon: 'archive', title: t('Archive')}),
					btn({icon: 'delete', title:t('Delete'), handler: () => { MailCtlr.destroy(this.value); }})
				),
				btn({icon: 'more_vert', menu: menu({},
					btn({icon: 'print', text: t('Print'), handler: () => {
						const data = this.value;
						this.body.el.print({title: data.sentAt + " - " + " - " + data.subject});
					}}),
					'-',
					btn({icon:'folder_open', text: t('Move')+'…'}),
					btn({icon: 'report', text: t('Report spam')}),
					'-',
					btn({icon: 'code', text: t('View source'),handler: () => {
						fileView.open({blobId:'community/email/src/'+this.value.id, type: 'text/plain', name: 'mailsrc.eml'}).show();
					}})
				)})
			),

			// in a conversation the thread message is the card already
			comp({style: inThread ? {} : {padding: '0 1.8rem'}, flex:'1'},
				comp({cls: inThread ? 'pad' : 'card pad'},
					comp({cls:'hbox',style:{marginTop:'1.6rem'}},
						this.avatar = avatar({
							style:{marginRight:'1.6rem'},
							displayName: 'Name',
							backgroundImage: undefined // add later for BIMI support
						}),

						comp({tagName: 'header', flex:'1'},
							comp({cls:'hbox'},
								this.from=comp({cls:'chips', flex:'1'}),
								comp({itemId:'mailboxes',cls:'badge',text:'Inbox'}),
								this.received=comp({tagName:'small', text: 'Date'}),
							),
							comp({cls:'hbox'},
								this.subject=comp({tagName:'h2', flex:'1',text: 'Loading'}),
								comp({text:'icons'})
							),
							comp({cls:'hbox'},
								comp({text:'To'}),
								this.to=comp({cls:'chips', flex:'1'})
							)
						)
					),
					hr(),
					this.body = comp({cls: 'textbox email pad', style:{paddingLeft:'59px'}, text: 'email'}),
					this.attachments = comp({cls: 'attachments pad'})
				)
			)
		);

		this.shadowRoot = this.body.el!.attachShadow({mode:'closed'});
		this.shadowRoot.innerHTML = `<style>blockquote {
			padding-left: 16px;
			margin-left: 0;
			margin-top: 4px;
			border-left: 3px solid rgba(0,0,0,0.38);
		}</style>`;
		this.shadowRoot.append(E('div').cls('textbox'));

		this.on('setvalue', ({newValue}) => {
			const record = newValue;
			this.avatar.displayName = record.from.length ? (record.from[0].name ?? record.from[0].email) : '?';
			this.subject.text = record.subject ?? t('No subject');
			this.from.items.replace(...this.emailChips(record.from ?? []));
			this.to.items.replace(...this.emailChips(record.to ?? []));
			this.received.text = Format.smartDateTime(record.receivedAt);

			this.shadowRoot.lastElementChild!.innerHTML = MailCtlr.emailText(record);

			this.attachments.items.clear();
			if(record.attachments) {
				this.attachments.items.add(...record.attachments.map((attachment: any) => {
					attachment.name ??= 'unnamed';
					return comp({
							tagName: 'a', listeners: {
								'render': ({target}) => {
									target.el.on('click', _e => {
										fileView.open(attachment).show();
									})
								}
							}
						},
						comp({cls: 'mime ' + `${attachment.name.split('.').pop()} ${attachment.type.split('/').join(' ')}`}),
						comp({
							tagName: 'span',
							html: `${attachment.name}<br> <small>${attachment.name.split('.').pop().toUpperCase()} &bull; ${Format.fileSize(attachment.size)}</small>`
						})
					)
				}));
			}

			if(record.id && !record.keywords?.$seen) {
				setTimeout(() => {
					// only when the message is still shown
					if(this.value?.id === record.id && this.rendered && this.el.isConnected && !this.hidden) {
						MailCtlr.markSeen([record], true);
					}
				}, 3000); // mark read on 3 seconds
			}
		});
	}

	private emailChips(emailAddresses: any[]) {
		if(!emailAddresses.length)
			return [btn({cls:'chip', text: t('No sender')})];
		return emailAddresses.map(emailAddress => {
			const label = emailAddress.name || emailAddress.email;
			return btn({cls:'chip', text: label, menu:menu({},
				comp({text:emailAddress.email}),
				hr(),
				btn({text:t('Copy address'), handler: () => {
					navigator.clipboard.writeText(emailAddress.email).then(
						() => Notifier.success(t('Copied to clipboard')),
						(e) => Notifier.error(e)
					);
				}}),
				btn({text:t('New E-mail'), handler: () => { MailCtlr.compose([emailAddress]).show(); }}),
				btn({text: t('Show contact'), handler: () => { MailCtlr.showContact(emailAddress.email); }}),
				hr(),
				btn({text: t('Search for')+ " '"+label+"'", hidden: !this.onSearchFrom, handler: () => {
					this.onSearchFrom?.(emailAddress.email);
				}})
			)});
		});
	}



	// private loadMail(form: CForm) {
	// 	let firstBq = form.dom.querySelector<HTMLElement>('blockquote[type="cite"]');
	// 	if (firstBq) { // collapse from first block quotes
	// 		let bqWrap = $.el('div'),
	// 			btn = $.el('a');
	// 		console.log(firstBq.child(0));
	// 		btn.html(firstBq.child(0).textContent);
	// 		btn.on('click', function() {this.hide(); bqWrap.cls('-hidden');});
	// 		firstBq.prepend(bqWrap);
	// 		bqWrap.prepend(btn);
	// 		bqWrap.cls('+hidden');
	// 		let elstack = [], next: any = firstBq;
	// 		while (next = next.nextSibling) {
	// 			elstack.push(next);
	// 		}
	// 		bqWrap.after(firstBq);
	// 		while (next = elstack.shift()) {
	// 			bqWrap.after(next);
	// 		}
	// 	}
	// }

}