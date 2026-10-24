import {comp, Component, Format} from "@intermesh/goui";
import {client} from "@intermesh/groupoffice-core";
import {t} from "./Index";

interface Quota {
	id: string
	resourceType: 'octets' | 'count'
	used: number
	hardLimit: number
	name: string
}

/**
 * Storage usage of the current account's mailbox. Hidden when the IMAP server reports no storage quota.
 */
export class QuotaBar extends Component {

	private label: Component
	private bar: Component
	/** Increased on every load() call, so an older response can't overwrite a newer one */
	private loadId = 0

	constructor() {
		super();
		this.cls = 'quota-bar';
		this.hidden = true;
		this.items.add(
			this.label = comp({cls: 'quota-label'}),
			this.bar = comp({tagName: 'progress'})
		);
	}

	async load(accountId: string) {
		const loadId = ++this.loadId;
		let quota: Quota | undefined;
		try {
			const r = await client.jmap('EmailAccount/quota', {accountId});
			// several quota roots can apply to the inbox, the fullest one limits it
			quota = (r.list as Quota[]).filter(q => q.resourceType === 'octets')
				.sort((a, b) => QuotaBar.usage(b) - QuotaBar.usage(a))[0];
		} catch (e) {
			// the bar is informative only, a server without quota support or an unreachable server just hides it
			console.warn(e);
		}
		if (loadId !== this.loadId) {
			return;
		}
		if (!quota) {
			this.hidden = true;
			return;
		}

		const progress = this.bar.el as HTMLProgressElement;
		progress.max = 1;
		progress.value = Math.min(QuotaBar.usage(quota), 1);
		this.el.classList.toggle('full', QuotaBar.usage(quota) >= 0.9);
		this.label.text = t("{used} of {limit} used")
			.replace('{used}', Format.fileSize(quota.used))
			.replace('{limit}', Format.fileSize(quota.hardLimit));
		this.title = quota.name;
		this.hidden = false;
	}

	/** Used part of the limit, a limit of 0 counts as full */
	private static usage(quota: Quota): number {
		return quota.hardLimit > 0 ? quota.used / quota.hardLimit : 1;
	}
}
