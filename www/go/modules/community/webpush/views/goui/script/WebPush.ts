import {client, jmapds} from "@intermesh/groupoffice-core";

function base64encode(v: ArrayBuffer) {
	return btoa(String.fromCharCode(...new Uint8Array(v)))
		.replace(/\+/g, '-')
		.replace(/\//g, '_')
		.replace(/=+$/, '');
}



export async function subscribe(registration: ServiceWorkerRegistration) {
	const applicationServerKey = client.session?.capabilities['urn:ietf:params:jmap:webpush-vapid'].applicationServerKey;
	if(!applicationServerKey)
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
		const allowed = await requestPermission();
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

	if(subscriptionChanged || !existing) { // then update server entity

		if(existing) {
			jmapds('PushSubscription').destroy(existing.id);
		}
		jmapds('PushSubscription').create({
			url: subscription.endpoint,
			deviceClientId,
			keys: {
				p256dh: base64encode(subscription.getKey("p256dh")!),
				auth: base64encode(subscription.getKey("auth")!)
			}
		})
	}

}

async function requestPermission(): Promise<boolean> {
	if (Notification.permission === 'granted') {
		return true;
	}
	if (Notification.permission === 'denied') {
		console.warn('Push notifications blocked by user.');
		return false;
	}

	const permission = await Notification.requestPermission();
	return permission === 'granted';
}

export async function unsubscribe(subscription: PushSubscription): Promise<void> {

	await jmapds('PushSubscription').query({
		filter: { deviceClientId: localStorage.getItem('deviceClientId') }
	}).then(async (result: any) => {
		const ids = result.ids;
		if (ids.length) {
			await jmapds('PushSubscription').destroy(ids);
		}
	});

	await subscription.unsubscribe();
}
