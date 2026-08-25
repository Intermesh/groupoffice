import {Account, client, modules, userDS,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";
import {OtpSettingsFieldset} from "./OtpSettingsFieldset.js";
import {enableOTP} from "./EnableOTPWindow.js";
import {Window} from "@intermesh/goui";


modules.register({
	package: "community",
	name: "otp",
	systemSettingsPanels: [Settings],
});

Account.patch(function() {
	this.form!.items.insert(-1, new OtpSettingsFieldset());
})

client.on("authenticated", async (ev) => {

	if(client.user.otp != null) {
		return;
	}

	const settings = modules.get("community", "otp")!.settings,
		isEnforced = settings.enforceForGroupId && client.user.groups.indexOf(settings.enforceForGroupId + "") > -1;

	if(!isEnforced) {
		return;
	}

	try {
		const secret = await enableOTP(client.user.username, settings.countDown, settings.block);

		try {
			await userDS.update(client.user.id, {otp: {secret}});
		} catch (e) {
			void Window.error(e);
		}
	}catch(e) {
		//user cancelled
	}
})
