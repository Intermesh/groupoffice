import {moduleSettings, appSystemSettings, client, modules} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings.js";
import {Settings} from "./Settings.js";

modules.register(  {
	package: "community",
	name: "davclient",
	entities: [
		"DavAccount"
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.davclient, "community", "davclient");

		client.on("authenticated",  ( {session}) => {

			//client.user.calendarPreferences ||= {};
			if(!session.capabilities["go:community:davclient"] || !session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}

			//const ui = new Main();
			// modules.addAccountSettingsPanel("community", "davclient", "davclient", t("DAV Accounts"), "manage_accounts", () => {
			// 	return new SystemSettings();
			// });
			//modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => ui);

			moduleSettings.addPanel(SystemSettings)

		});
	}
});

client.on("authenticated",  ({session}) => {
	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "davclient", Settings);
	}
});

