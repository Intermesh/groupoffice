import {appSystemSettings, client, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {CalendarEvent, CalendarItem, onlineMeetingServices} from "@intermesh/community/calendar";
import {a} from "@intermesh/goui";
client.on("authenticated",  ({session}) => {

	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "addressbook", Settings);
	}
});