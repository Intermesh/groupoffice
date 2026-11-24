import {client, modules} from "@intermesh/groupoffice-core";
import {t as coreT} from "@intermesh/goui";
import {PreferencesPanel} from "./PreferencesPanel";
import {isTurnedOffOnDevice, subscribe} from "./WebPush";

export const t = (key: string, p = 'community', m = 'webpush') => coreT(key, p, m);

modules.register({
	package: "community",
	name: "webpush",
	entities: [
		"PushSubscription"
	],
	userSettingsPanels: [PreferencesPanel],
	async init() {

		if (!window.isSecureContext) {
			console.warn("Notifications only work in secure context");
			return;
		}
		if ('serviceWorker' in navigator) {
			const registration = await navigator.serviceWorker.register('sw.js', {
				updateViaCache: 'none'
			});
			if (!('PushManager' in window)) {
				return;
			}

			// The server key comes with the session, so subscribe after logging in. Subscribing fails when the browser
			// can't push, like in a private window; the rest of GroupOffice works without it.
			const trySubscribe = () => {
				if (!client.session?.capabilities["urn:ietf:params:jmap:webpush-vapid"] || isTurnedOffOnDevice()) {
					return;
				}
				// Subscribing needs an active service worker, which a new one isn't yet
				navigator.serviceWorker.ready
					.then(ready => subscribe(ready))
					.catch(e => console.warn("Couldn't subscribe to push messages", e));
			};
			client.on("authenticated", trySubscribe);
			if (client.session) {
				trySubscribe();
			}
		}
	}
});

