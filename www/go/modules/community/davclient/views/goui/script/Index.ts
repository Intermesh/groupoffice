import {client, modules} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings.js";

modules.register(  {
	package: "community",
	name: "davclient",
	entities: [
		"DavAccount"
	],
	init () {

		client.on("authenticated",  ( {session}) => {

			//client.user.calendarPreferences ||= {};
			if(!session.capabilities["go:community:davclient"] || !session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}

			//const ui = new Main();
			modules.addAccountSettingsPanel("community", "davclient", "davclient", t("DAV Accounts"), "manage_accounts", () => {
				return new SystemSettings();
			});
			//modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => ui);

		});
	}
});