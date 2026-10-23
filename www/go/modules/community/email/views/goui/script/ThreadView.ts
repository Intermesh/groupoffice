import {avatar, btn, comp, Component, ComponentEventMap, DefaultEntity, Format, t, tbar} from "@intermesh/goui";
import {EmailView} from "./EmailView";
import {jmapds} from "@intermesh/groupoffice-core";

export interface ThreadViewEventMap extends ComponentEventMap {
	close: {}
	searchfrom: {email: string}
}

/**
 * One message of a conversation. Collapsed it shows the sender, date and preview; expanded the full email.
 */
class ThreadMessage extends Component {

	private emailView?: EmailView
	private summary: Component
	private _expanded = false

	constructor(private email: any, private threadView: ThreadView) {
		super();
		this.cls = 'thread-message';

		const from = email.from?.length ? (email.from[0].name || email.from[0].email) : t('No sender');
		this.summary = comp({cls: 'hbox thread-message-summary', style: {cursor: 'pointer', padding: '0.8rem 1.8rem', alignItems: 'center', gap: '1.2rem'}},
			avatar({displayName: from}),
			comp({flex: '1', style: {minWidth: '0'}},
				comp({cls: 'hbox'},
					comp({tagName: 'h4', flex: '1', text: from, style: {margin: '0', fontWeight: email.keywords?.$seen ? 'normal' : 'bold'}}),
					comp({tagName: 'small', text: email.receivedAt ? Format.smartDateTime(email.receivedAt) : ''})
				),
				comp({tagName: 'div', cls: 'clamp', text: email.preview ?? '', style: {opacity: '0.7'}})
			)
		);
		this.summary.el.addEventListener('click', () => this.expanded = true);
		this.items.add(this.summary);
	}

	get expanded() {
		return this._expanded;
	}

	set expanded(expanded: boolean) {
		this._expanded = expanded;
		if (expanded && !this.emailView) {
			this.emailView = new EmailView(true);
			this.emailView.onCollapse = () => this.expanded = false;
			this.emailView.onSearchFrom = (email) => this.threadView.fire('searchfrom', {email});
			this.items.add(this.emailView);
			this.emailView.value = this.email;
		}
		this.summary.hidden = expanded;
		if (this.emailView) {
			this.emailView.hidden = !expanded;
		}
	}
}

/**
 * Shows a conversation: read messages collapsed, unread messages and the latest message expanded.
 */
export class ThreadView extends Component<ThreadViewEventMap> {

	private header: Component
	private subject: Component
	private count: Component
	private messages: Component
	private empty: Component
	private loadId = 0

	constructor() {
		super();
		this.cls = 'vbox';
		this.flex = '1';

		this.items.add(
			this.header = tbar({hidden: true},
				btn({title: t('Close'), icon: 'close', handler: () => this.fire('close', {})}),
				this.subject = comp({tagName: 'h3', flex: '1', style: {margin: '0'}}),
				this.count = comp({cls: 'badge'}),
				btn({title: t('Expand all'), icon: 'unfold_more', handler: () => this.setAllExpanded(true)}),
				btn({title: t('Collapse all'), icon: 'unfold_less', handler: () => this.setAllExpanded(false)})
			),
			this.messages = comp({flex: '1', cls: 'scroll'}),
			this.empty = comp({cls: 'empty-state', flex: '1', style: {display: 'flex', alignItems: 'center', justifyContent: 'center'}},
				comp({tagName: 'p', text: t('Select a conversation')})
			)
		);
	}

	private setAllExpanded(expanded: boolean) {
		const items = [...this.messages.items].filter(i => i instanceof ThreadMessage) as ThreadMessage[];
		items.forEach((item, i) => {
			// always keep the latest message open
			item.expanded = expanded || i === items.length - 1;
		});
	}

	clear() {
		this.loadId++;
		this.messages.items.clear();
		this.header.hidden = true;
		this.empty.hidden = false;
	}

	async load(thread: DefaultEntity) {
		const loadId = ++this.loadId;
		this.messages.items.clear();
		this.empty.hidden = true;
		this.header.hidden = false;
		this.subject.text = t('Loading') + '…';
		this.count.hidden = true;

		const r = await jmapds('Email').get(thread.emailIds as string[]);
		if (loadId !== this.loadId) {
			return; // another thread was opened in the meantime
		}
		const emails = (r.list as any[]).sort((a, b) =>
			String(a.receivedAt ?? '').localeCompare(String(b.receivedAt ?? '')));

		if (!emails.length) {
			this.subject.text = t('Conversation not found');
			return;
		}

		this.subject.text = emails[0].subject || t('No subject');
		this.count.text = String(emails.length);
		this.count.hidden = emails.length < 2;
		this.count.title = emails.length + ' ' + t('messages');

		emails.forEach((email, i) => {
			const item = new ThreadMessage(email, this);
			this.messages.items.add(item);
			item.expanded = i === emails.length - 1 || !email.keywords?.$seen;
		});

		// scroll to the first message that needs attention
		const first = this.messages.items.find(i => (i as ThreadMessage).expanded);
		if (first && first !== this.messages.items.get(0)) {
			first.el.scrollIntoView({block: 'start'});
		}
	}
}
