import {client, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {CalendarEvent, CalendarItem, onlineMeetingServices} from "@intermesh/community/calendar";


modules.register({
	package: "community",
	name: "oidc",
	init: () => {
		modules.addSystemSettingsPanel("community", "oidc", "oidc", "OIDC", "star", () => {
			return new Settings();
		});
	},
	entities: [
		{
			name: "OIDConnectClient"
		}
	]
})

export const OIDConnectClientDS = new JmapDataSource("OIDConnectClient");