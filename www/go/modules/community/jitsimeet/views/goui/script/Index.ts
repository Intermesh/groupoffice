import {client, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {CalendarEvent, CalendarItem, onlineMeetingServices} from "@intermesh/community-calendar";
import {t, translate} from "@intermesh/goui";



/**
 * Random room name of 16 hexadecimal characters, like the server creates in Settings::createMeetingUri().
 * Jitsi Meet doesn't accept every character in a room name, such as "-".
 */
function randomRoom() {
	return Array.from(crypto.getRandomValues(new Uint8Array(8)), b => b.toString(16).padStart(2, '0')).join('');
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
				modules.addSystemSettingsPanel("community", "jitsimeet", "jitsimeet", "Jitsi Meet", "video_call", () => {
					return new Settings();
				});
			}

			onlineMeetingServices.register("Jitsi Meet", async (calendarEventForm) => {

				translate.setDefaultModule("community", "jitsimeet");

				const m = modules.get('community', 'jitsimeet')!;

				const room = randomRoom();

				let uri;
				if (!m.settings.videoJwtEnabled && !m.settings.jaasEnabled) {
					uri = m.settings.videoUri + room;
				}else {
					const response = await client.jmap("community/jitsimeet/Auth/generateJWT", {room}, 'pJwt');
					uri = m.settings.jaasEnabled
						? m.settings.videoUri.replace(/\/$/, '') + '/' + m.settings.jaasAppId + '/' + room + '?jwt=' + response.jwt
						: m.settings.videoUri + room + '?jwt=' + response.jwt
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
})