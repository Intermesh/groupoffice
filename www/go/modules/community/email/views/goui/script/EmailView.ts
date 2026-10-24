import {
	btn,
	comp,
	Component,
	t,
	E,
	menu,
	Format,
	DataSourceForm,
	avatar, hr, Avatar, Button, Notifier
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
	private recipients: Button
	private details: Component
	private received: Component
	private subject: Component
	private body: Component
	private attachments: Component
	private shadowRoot: ShadowRoot

	constructor(inThread = false) {
		super(jmapds('Email'));
		this.cls = 'vbox';
		this.flex = '1';

		let header: Component;
		this.items.add(
			// in a conversation the thread message is the card already
			comp({style: inThread ? {} : {padding: '0 1.8rem'}, flex:'1'},
				comp({cls: 'email-message' + (inThread ? ' in-thread' : ' card')},
					// in a conversation the thread header shows the subject once
					this.subject = comp({tagName: 'h2', cls: 'email-subject', hidden: inThread}),
					header = comp({tagName: 'header', cls: 'email-header'},
						this.avatar = avatar({
							displayName: '?',
							backgroundImage: undefined // add later for BIMI support
						}),
						comp({cls: 'email-header-meta'},
							this.from = comp({cls: 'email-from'}),
							this.recipients = btn({cls: 'email-recipients', title: t('Show details'),
								menu: menu({cls: 'email-details'},
									this.details = comp({})
								)
							})
						),
						comp({cls: 'email-header-tools'},
							this.received = comp({tagName: 'small'}),
							btn({icon: 'reply', title: t('Reply'), handler: () => { MailCtlr.reply(this.value).show(); } }),
							btn({icon: 'more_vert', title: t('More options'), menu: menu({},
								btn({icon: 'reply_all', text: t('Reply all'), handler: () => { MailCtlr.reply(this.value, true).show(); } }),
								btn({icon: 'forward', text: t('Forward'), handler: () => { MailCtlr.forward(this.value).show(); } }),
								'-',
								btn({icon: 'archive', text: t('Archive')}),
								btn({icon: 'delete', text: t('Delete'), handler: () => { MailCtlr.destroy(this.value); }}),
								'-',
								btn({icon: 'print', text: t('Print'), handler: () => {
									const data = this.value;
									this.body.el.print({title: data.sentAt + " - " + " - " + data.subject});
								}}),
								btn({icon:'folder_open', text: t('Move')+'…'}),
								btn({icon: 'report', text: t('Report spam')}),
								'-',
								btn({icon: 'code', text: t('View source'),handler: () => {
									fileView.open({blobId:'community/email/src/'+this.value.id, type: 'text/plain', name: 'mailsrc.eml'}).show();
								}})
							)}),
							...(inThread ? [] : [btn({icon: 'close', title: t('Close'), handler: () => { this.clear(); } })])
						)
					),
					this.body = comp({cls: 'textbox email email-body', text: 'email'}),
					this.attachments = comp({cls: 'attachments email-attachments'})
				)
			)
		);

		if(inThread) {
			// like the collapsed summary, a click on the header (not on its buttons) folds the message
			header.el.title = t('Collapse');
			header.el.tabIndex = 0;
			header.el.setAttribute('role', 'button');
			header.el.setAttribute('aria-expanded', 'true');
			header.el.addEventListener('click', (e) => {
				if(!(e.target as HTMLElement).closest('button, a')) {
					this.onCollapse?.();
				}
			});
			header.el.addEventListener('keydown', (e) => {
				if(e.target === header.el && (e.key === 'Enter' || e.key === ' ')) {
					e.preventDefault();
					this.onCollapse?.();
				}
			});
		}

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
			this.avatar.displayName = record.from?.length ? (record.from[0].name || record.from[0].email) : '?';
			this.subject.text = record.subject || t('No subject');
			this.from.items.replace(...this.senders(record.from ?? []));
			this.renderRecipients(record);
			this.received.text = record.receivedAt ? Format.smartDateTime(record.receivedAt) : '';
			this.received.title = record.receivedAt ? Format.dateTime(record.receivedAt) : '';

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

	/**
	 * The sender as bold name followed by the address, like the collapsed summary
	 */
	private senders(emailAddresses: any[]) {
		if(!emailAddresses.length)
			return [comp({tagName: 'b', text: t('No sender')})];
		return emailAddresses.map(emailAddress => {
			const sender = this.emailChips([emailAddress], 'email-sender')[0];
			return emailAddress.name ?
				comp({cls: 'email-sender-line'}, sender, comp({tagName: 'small', text: '<' + emailAddress.email + '>'})) :
				sender;
		});
	}

	/**
	 * Compact "to Alice, Bob" line; its menu lists all headers with an action menu per address
	 */
	private renderRecipients(record: any) {
		const recipients = [...(record.to ?? []), ...(record.cc ?? []), ...(record.bcc ?? [])];
		this.recipients.text = t('to') + ' ' + (recipients.length ?
			recipients.map((a: any) => a.name || a.email).join(', ') :
			t('undisclosed recipients'));

		const rows: [string, Component[]][] = [
			[t('From'), this.emailChips(record.from ?? [], 'email-address')],
			[t('Reply-to'), record.replyTo?.length ? this.emailChips(record.replyTo, 'email-address') : []],
			[t('To'), record.to?.length ? this.emailChips(record.to, 'email-address') : []],
			[t('Cc'), record.cc?.length ? this.emailChips(record.cc, 'email-address') : []],
			[t('Bcc'), record.bcc?.length ? this.emailChips(record.bcc, 'email-address') : []],
			// the Date header of the message, the header line shows when it arrived
			[t('Date'), record.sentAt || record.receivedAt ? [comp({text: Format.dateTime(record.sentAt || record.receivedAt)})] : []],
			[t('Subject'), [comp({text: record.subject || t('No subject')})]]
		];
		this.details.items.replace(...rows.filter(([, value]) => value.length).map(([label, value]) =>
			comp({cls: 'email-details-row'},
				comp({tagName: 'small', text: label}),
				comp({cls: 'chips'}, ...value)
			)
		));
	}

	private emailChips(emailAddresses: any[], cls = 'chip') {
		if(!emailAddresses.length)
			return [btn({cls, text: t('No sender')})];
		return emailAddresses.map(emailAddress => {
			const label = emailAddress.name || emailAddress.email;
			const actions = menu({},
				comp({cls: 'email-address-head'},
					...(emailAddress.name ? [comp({tagName: 'b', text: emailAddress.name})] : []),
					comp({tagName: 'small', text: emailAddress.email})
				),
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
			);
			// in the details popup the address sits in a row, so goui doesn't close that popup after an action
			actions.el.addEventListener('click', (e) => {
				if((e.target as HTMLElement).closest('button')) {
					this.recipients.menu?.close();
				}
			});
			// the details popup shows the full "Name <address>", every row in the same plain format
			const text = cls === 'email-address' && emailAddress.name ? `${emailAddress.name} <${emailAddress.email}>` : label;
			return btn({cls, text, menu: actions});
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