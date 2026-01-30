import {appSystemSettings, client, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {CalendarEvent, CalendarItem, onlineMeetingServices} from "@intermesh/community/calendar";
import {SystemSettings} from "./SystemSettings";


modules.register({
	package: "community",
	name: "oidc",
	init: () => {

		client.on("authenticated",  ({session}) => {
			if (!session.capabilities["go:community:oidc"]) {
				// User has no access to this module
				return;
			}

			if (session.capabilities["go:community:oidc"].mayManage) {
				// @deprecated
				modules.addSystemSettingsPanel("community", "oidc", "oidc", "OIDC", "app_registration", () => {
					return new SystemSettings();
				});
			}

			if (session.isAdmin) {
				appSystemSettings.addPanel("community", "oidc", Settings);
			}

		});
	},
	entities: [
		{
			name: "OIDConnectClient"
		}
	]
})

export const OIDConnectClientDS = new JmapDataSource("OIDConnectClient");