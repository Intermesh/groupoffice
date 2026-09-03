import {client, modules} from "@intermesh/groupoffice-core";
import {t as coreT} from "@intermesh/goui";
import {PreferencesPanel} from "./PreferencesPanel";
import {subscribe} from "./WebPush";

export const t = (key:string,p='community',m='calendar') => coreT(key, p,m);

modules.register(  {
	package: "community",
	name: "webpush",
	entities: [
		"PushSubscription"
	],
	userSettingsPanels: [PreferencesPanel],
	async init () {
		//client.on("authenticated",  ({session}) => {

			// if (!session.capabilities["urn:ietf:params:jmap:webpush-vapid"]) {
			// 	return;
			// }
			if ('serviceWorker' in navigator) {
				const registration = await navigator.serviceWorker.register('sw.js', {
					updateViaCache: 'none'
				});
				registration.addEventListener('updatefound', () => {
					const worker = registration.installing;

					console.log('New service worker found:', worker);

					worker?.addEventListener('statechange', () => {
						console.log('New SW state:', worker.state);
					});
				})
				if ( ('PushManager' in window)) {
					subscribe(registration);
				}
			}

		//});
	}
});

