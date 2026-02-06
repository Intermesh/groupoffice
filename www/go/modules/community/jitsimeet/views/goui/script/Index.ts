import {appSystemSettings, client, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {SystemSettings} from "./SystemSettings.js";
import {onlineMeetingServices} from "@intermesh/community/calendar";
import {t} from "@intermesh/goui";



function b64UrlEncode (data:string) {
	const base64 = btoa(data);
	return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

modules.register({
	package: "community",
	name: "jitsimeet",
	init: () => {

		client.on("authenticated",  ({session}) => {
			if(!session.capabilities["go:community:jitsimeet"]) {
				// User has no access to this module
				return;
			}

			if(session.capabilities["go:community:jitsimeet"].mayManage) {
				// @deprecated
				modules.addSystemSettingsPanel("community", "jitsimeet", "jitsimeet", "Jitsi Meet", "video_call", () => {
					return new SystemSettings();
				});
			}

			onlineMeetingServices.register("Jitsi Meet", async (calendarEventForm) => {
				const m = modules.get('community', 'jitsimeet')!;

				const room = b64UrlEncode(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(8))));

				let uri;
				if (!m.settings.videoJwtEnabled) {
					uri = m.settings.videoUri + room;
				}else {
					const response = await client.jmap("community/jitsimeet/Auth/generateJWT", {room}, 'pJwt');
					uri = m.settings.videoUri + room + '?jwt=' + response.jwt
				}

				const calendarEvent = calendarEventForm.value;

				let description = calendarEvent.description ?? "";

				if(description.length > 0) {
					description += "\n\n";
				}

				description += "--------------------------------\n" + t("Join the online meeting now") + ":\n\n" + uri + "\n--------------------------------\n\n";

				calendarEventForm.patch({location: uri, description});
			});
		});
	}
});

client.on("authenticated",  ({session}) => {

	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "jitsimeet", Settings);
	}
});
