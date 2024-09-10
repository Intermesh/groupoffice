import {client, modules} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings";

modules.register(  {
	package: "community",
	name: "davclient",
	entities: [
		"DavAccount"
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.davclient, "community", "davclient");

		client.on("authenticated",  (client, session) => {

			//client.user.calendarPreferences ||= {};
			if(!session.capabilities["go:community:davclient"] || !session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}

			//const ui = new Main();
			modules.addSystemSettingsPanel("community", "davclient", "davclient", t("DAV Accounts"), "manage_accounts", () => {
				return new SystemSettings();
			});
			//modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => ui);

		});
	}
});