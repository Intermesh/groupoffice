import {btn, Button, comp, Component, fieldset, tbar, Window} from "@intermesh/goui";
import {AppSettingsPanel, client, User} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";
import {DeviceState, getDeviceState, onDeviceStateChange, turnOff, turnOn} from "./WebPush.js";

/**
 * Turn push notifications on or off for the browser the user works in
 */
export class PreferencesPanel extends AppSettingsPanel {

	private status: Component;
	private onButton: Button;
	private offButton: Button;

	constructor() {
		super();
		this.title = t("Push notifications");
		this.icon = "notifications";

		this.items.add(
			fieldset({legend: t("This device")},
				this.status = comp({tagName: "p"}),
				tbar({},
					this.onButton = btn({
						cls: "filled primary",
						icon: "notifications_active",
						text: t("Turn on for this device"),
						handler: () => this.change(turnOn)
					}),
					this.offButton = btn({
						cls: "outlined",
						icon: "notifications_off",
						text: t("Turn off for this device"),
						handler: () => this.change(turnOff)
					})
				)
			)
		);

		// The browser confirms a new subscription a moment after turning it on
		const stop = onDeviceStateChange(() => void this.refresh());
		this.on("remove", stop);
	}

	/**
	 * A subscription belongs to the browser of the logged-in user, so other users' settings don't show it
	 */
	public isAllowed(user: User): boolean {
		return user.id == client.user.id && super.isAllowed(user);
	}

	public async load(user: User) {
		await this.refresh();
	}

	private async change(fn: () => Promise<unknown>) {
		this.mask();
		try {
			await fn();
		} catch (e) {
			// Like in a private window, where browsers have no push
			console.error(e);
			void Window.error(t("This browser couldn't turn on push notifications. Private windows don't support them."));
		} finally {
			this.unmask();
		}
		await this.refresh();
	}

	private async refresh() {
		let state: DeviceState;
		try {
			state = await getDeviceState();
		} catch (e) {
			console.error(e);
			state = "off";
		}

		const texts: Record<DeviceState, string> = {
			unsupported: t("This browser can't receive push notifications. They need a secure (HTTPS) connection and don't work in private windows."),
			unavailable: t("Push notifications aren't available for your account."),
			blocked: t("Notifications are blocked for this site in the browser. Allow them in the site settings of the browser, then reload the page."),
			off: t("Push notifications are off for this device."),
			pending: t("Push notifications are being turned on. Waiting for this browser to confirm them. If this message stays, the push service can't reach this browser."),
			on: t("Push notifications are on for this device. Reminders and other alerts also show when GroupOffice isn't open, as long as the browser runs.")
		};
		this.status.text = texts[state];

		this.onButton.hidden = !["off", "blocked"].includes(state);
		this.onButton.disabled = state == "blocked";
		this.offButton.hidden = !["on", "pending"].includes(state);
	}
}
