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
			.then(() => stateListeners.forEach(fn => fn()))
			.catch(e => console.error("Couldn't verify the push subscription", e));
	});
}

const stateListeners = new Set<() => void>();

/**
 * Call a function when this device confirmed its subscription
 *
 * @return a function that stops the calls
 */
export function onDeviceStateChange(fn: () => void): () => void {
	stateListeners.add(fn);
	return () => stateListeners.delete(fn);
}

/**
 * Whether this browser can receive push messages from GroupOffice
 *
 * - unsupported: the browser has no push, or the page isn't served over HTTPS
 * - unavailable: the user has no access to the web push module
 * - blocked: the user blocked notifications for this site in the browser
 * - off: not subscribed, or turned off for this device
 * - pending: subscribed, but the browser didn't confirm it yet
 * - on: push messages reach this browser
 */
export type DeviceState = "unsupported" | "unavailable" | "blocked" | "off" | "pending" | "on";

/**
 * Remembers in this browser that the user turned push off, so logging in doesn't subscribe again
 */
const TURNED_OFF_KEY = 'webpushTurnedOff';

export function isTurnedOffOnDevice(): boolean {
	try {
		return localStorage.getItem(TURNED_OFF_KEY) === "1";
	} catch (e) {
		return false;
	}
}

function getApplicationServerKey(): string | undefined {
	return client.session?.capabilities['urn:ietf:params:jmap:webpush-vapid']?.applicationServerKey;
}

function getDeviceClientId(): string {
	let deviceClientId = localStorage.getItem('deviceClientId');
	if (!deviceClientId) {
		deviceClientId = crypto.randomUUID();
		localStorage.setItem('deviceClientId', deviceClientId);
	}
	return deviceClientId;
}

async function getBrowserSubscription(): Promise<PushSubscription | null> {
	const registration = await navigator.serviceWorker.getRegistration();
	return registration ? registration.pushManager.getSubscription() : null;
}

export async function getDeviceState(): Promise<DeviceState> {
	if (!window.isSecureContext || !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
		return "unsupported";
	}
	if (!getApplicationServerKey()) {
		return "unavailable";
	}
	if (Notification.permission === 'denied') {
		return "blocked";
	}
	const subscription = await getBrowserSubscription();
	if (!subscription || isTurnedOffOnDevice()) {
		return "off";
	}
	// From the server, because the data source doesn't know when the server confirmed it
	const deviceClientId = getDeviceClientId(),
		response = await client.jmap("PushSubscription/get", {}),
		mine = (response.list as any[]).find(s => s.deviceClientId === deviceClientId && s.url === subscription.endpoint);
	if (!mine) {
		return "off";
	}
	return mine.verified ? "on" : "pending";
}

/**
 * Turn push on for this device. Call it from a click, because browsers only ask for permission after a user action.
 *
 * @return false when the user didn't allow notifications
 */
export async function turnOn(): Promise<boolean> {
	if (Notification.permission !== 'granted' && await Notification.requestPermission() !== 'granted') {
		return false;
	}
	localStorage.removeItem(TURNED_OFF_KEY);
	return subscribe(await navigator.serviceWorker.ready);
}

/**
 * Turn push off for this device, also for later logins
 */
export async function turnOff(): Promise<void> {
	localStorage.setItem(TURNED_OFF_KEY, "1");
	await unsubscribe(await getBrowserSubscription());
}

/**
 * Subscribe this browser and save the subscription on the server
 *
 * @return false when there is no subscription, because the user didn't allow notifications
 */
export async function subscribe(registration: ServiceWorkerRegistration): Promise<boolean> {
	listenForVerification();

	const applicationServerKey = getApplicationServerKey();
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

	const deviceClientId = getDeviceClientId();

	const result = await jmapds('PushSubscription').get();
	const existing = result.list.find(s => s.deviceClientId === deviceClientId);

	// Also when the browser got a new address from its push service
	if (subscriptionChanged || !existing || existing.url !== subscription.endpoint) { // then update server entity

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
	return true;
}

/**
 * Delete the subscriptions of this device on the server and in the browser
 */
export async function unsubscribe(subscription: PushSubscription | null): Promise<void> {

	await jmapds('PushSubscription').query({
		filter: {deviceClientId: localStorage.getItem('deviceClientId')}
	}).then(async (result: any) => {
		const ids = result.ids;
		if (ids.length) {
			await jmapds('PushSubscription').destroy(ids);
		}
	});

	await subscription?.unsubscribe();
}
