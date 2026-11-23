import {client, jmapds, main} from "@intermesh/groupoffice-core";

function base64encode(v: ArrayBuffer) {
	return btoa(String.fromCharCode(...new Uint8Array(v)))
		.replace(/\+/g, '-')
		.replace(/\//g, '_')
		.replace(/=+$/, '');
}


let listening = false;

/**
 * The service worker gets the verification code of a new subscription by push and passes it on, because it has no
 * access token for the API. Saving it proves the subscription belongs to this browser.
 */
function listenForVerification() {
	if (listening) {
		return;
	}
	listening = true;
	navigator.serviceWorker.addEventListener('message', (ev: MessageEvent) => {
		if (ev.data?.type !== 'pushVerification') {
			return;
		}
		void jmapds('PushSubscription').update(ev.data.pushSubscriptionId, {verificationCode: ev.data.verificationCode})
			.catch(e => console.error("Couldn't verify the push subscription", e));
	});
}

export async function subscribe(registration: ServiceWorkerRegistration) {
	listenForVerification();

	const applicationServerKey = client.session?.capabilities['urn:ietf:params:jmap:webpush-vapid'].applicationServerKey;
	if (!applicationServerKey)
		return false;

	let subscriptionChanged = false;
	let subscription = await registration.pushManager.getSubscription();

	if (subscription) {
		const currentKey = subscription.options.applicationServerKey;

		const currentKeyBase64 = currentKey ? base64encode(currentKey) : null;

		if (currentKeyBase64 !== applicationServerKey) {
			await subscription.unsubscribe();
			subscription = null;
		}
	}

	if (!subscription) {
		// The same question as for the desktop notifications, so the user is asked once
		const allowed = await main.notifier.initNotifications();
		if (!allowed) {
			return false;
		}

		subscription = await registration.pushManager.subscribe({
			userVisibleOnly: true,
			applicationServerKey
		});
		subscriptionChanged = true;
	}

	let deviceClientId = localStorage.getItem('deviceClientId');
	if (!deviceClientId) {
		deviceClientId = crypto.randomUUID();
		localStorage.setItem('deviceClientId', deviceClientId);
	}

	const result = await jmapds('PushSubscription').get();
	const existing = result.list.find(s => s.deviceClientId === deviceClientId);

	if (subscriptionChanged || !existing) { // then update server entity

		if (existing) {
			await jmapds('PushSubscription').destroy(existing.id);
		}
		await jmapds('PushSubscription').create({
			url: subscription.endpoint,
			deviceClientId,
			keys: {
				p256dh: base64encode(subscription.getKey("p256dh")!),
				auth: base64encode(subscription.getKey("auth")!)
			}
		})
	}

}

export async function unsubscribe(subscription: PushSubscription): Promise<void> {

	await jmapds('PushSubscription').query({
		filter: {deviceClientId: localStorage.getItem('deviceClientId')}
	}).then(async (result: any) => {
		const ids = result.ids;
		if (ids.length) {
			await jmapds('PushSubscription').destroy(ids);
		}
	});

	await subscription.unsubscribe();
}
