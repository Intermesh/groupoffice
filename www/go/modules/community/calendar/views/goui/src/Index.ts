import {modules} from "@go-core/Modules.js";
import {Main} from "./Main.js";
import {router} from "@go-core/Router.js";
import {jmapstore} from "@goui/jmap/JmapStore.js";

export const calendarStore = jmapstore({
	entity:'Calendar',
	properties: ['id', 'name', 'color', 'isVisible', 'isSubscribed'],
	sort: [{property:'name'}]
})

modules.register(  {
	package: "community",
	name: "calendar",
	init () {

		let ui: Main;

		router.add(/^calendar\/year\/(\d+)$/, (year) => {
			modules.openMainPanel("calendar");
			ui.data.view = 'year';
			ui.tabs.change('year');
		}).add(/^calendar\/month\/(\d+)$/, (year, month) => {
			modules.openMainPanel("calendar");
			ui.data.view = 'month';
			ui.tabs.change('month');
		}).add(/^calendar\/week\/(\d+)$/, (year, week) => {
			modules.openMainPanel("calendar");
			ui.data.view = 'week';
			ui.tabs.change('week');
		});

		modules.addMainPanel("calendar", "Calendar", () => {
			ui = new Main();
			return ui;
		});
	}
});