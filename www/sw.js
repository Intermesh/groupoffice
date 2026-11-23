/**
 * Service worker of the web push module
 *
 * It shows the push messages of GroupOffice as notifications. The verification code of a new subscription is passed
 * to the open GroupOffice page, which saves it, because the service worker has no access token for the API.
 */

self.addEventListener('install', () => {
	self.skipWaiting();
});

self.addEventListener('activate', event => {
	event.waitUntil(self.clients.claim());
});

self.addEventListener('push', event => {
	const data = event.data ? event.data.json() : {};

	if (data.verificationCode) {
		event.waitUntil(
			self.clients.matchAll({type: 'window', includeUncontrolled: true}).then(windows => {
				windows.forEach(w => w.postMessage({
					type: 'pushVerification',
					pushSubscriptionId: data.pushSubscriptionId,
					verificationCode: data.verificationCode
				}));
			})
		);
		return;
	}

	event.waitUntil(
		self.registration.showNotification(data.title || 'GroupOffice', {
			body: data.body || '',
			icon: data.icon || 'favicon.ico',
			// The same alert replaces its earlier notification
			tag: data.tag || (data.id ? 'alert-' + data.id : undefined),
			data
		})
	);
});

self.addEventListener('notificationclick', event => {
	event.notification.close();
	const url = (event.notification.data && event.notification.data.url) || self.registration.scope;

	event.waitUntil(
		self.clients.matchAll({type: 'window', includeUncontrolled: true}).then(windows => {
			const open = windows.find(w => w.url.startsWith(self.registration.scope));
			if (open) {
				return open.focus();
			}
			return self.clients.openWindow(url);
		})
	);
});
