function post(data) {
	return fetch('api/', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Authorization': `Bearer ${token}`,
		},
		credentials: 'include',
		body: JSON.stringify(data),
	})
		.then(async response => {
			console.log('POST RESPONSE', response.status, response.statusText);

			const text = await response.text();
			console.log('POST BODY', text);

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}: ${text}`);
			}

			return response;
		})
		.catch(error => {
			console.error('POST FAILED', error);
			throw error;
		});
}

self.addEventListener('push', (event) => {
	const data = event.data?.json();

	if(data.verificationCode) { // PushVerification
		console.log(data.verificationCode);
		event.waitUntil(
			post([
				["PushSubscription/set", {
					"update": {
						[data.pushSubscriptionId]: {"verificationCode": data.verificationCode}
					}
				}, "sw0"]
			])
		);
		return;
	}

	event.waitUntil(
		self.registration.showNotification(data.title, {
			body: data.text,
			icon: data.icon ?? 'favicon.ico',
			tag: data.tag ?? data.id, // deduplicates if same notification arrives twice
			data,
			actions: data.actions ?? [
				{ action: 'accept', title: '✓ Accept' },
				{ action: 'decline', title: '✗ Decline' },
			],
		})
	);
});






 // when debuggiong
self.addEventListener('install', event => {
	console.log('NEW SW INSTALL');

	self.skipWaiting();
});

// self.addEventListener('notificationclick', (event) => {
// 	event.notification.close();
//
// 	const payload = event.notification.data;
// 	//const action = event.action; // 'accept' | 'decline' | '' (body click)
//
// 	event.waitUntil(
// 		clients.openWindow(payload?.url ?? '/')
// 	);

	// event.waitUntil((async () => {
	// 	// 1. If there's a headless handler for this action, call it directly
	// 	const headless = action && payload.headlessActions?.[action];
	// 	if (headless) {
	// 		try {
	// 			await fetch(headless.url, {
	// 				method: headless.method,
	// 				headers: {
	// 					'Content-Type': 'application/json',
	// 					'Authorization': `Bearer ${headless.authToken}`,
	// 				},
	// 				body: headless.body ? JSON.stringify(headless.body) : undefined,
	// 			});
	// 			return; // done, no page needed
	// 		} catch {
	// 			// fall through to page if fetch fails
	// 		}
	// 	}
	//
	// 	// 2. Try to hand off to an open page
	// 	const windowClients = await clients.matchAll({ type: 'window', includeUncontrolled: true });
	// 	const existing = windowClients.find(c => c.url.startsWith(self.location.origin));
	// 	if (existing) {
	// 		await existing.focus();
	// 		existing.postMessage({
	// 			type: 'NOTIFICATION_CLICK',
	// 			action,
	// 			payload,
	// 		});
	// 		return;
	// 	}
	//
	// 	// 3. No page open — navigate
	// 	if (payload.actionUrl && clients.openWindow) {
	// 		await clients.openWindow(payload.actionUrl);
	// 	}
	// })());
//});