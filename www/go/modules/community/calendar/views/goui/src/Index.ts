import {modules} from "@go-core/Modules.js";
import {CalendarMain} from "./CalendarMain";
import {router} from "@go-core/Router.js";

modules.register(  {
	package: "community",
	name: "calendar",
	init () {

		let ui: CalendarMain;

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
			ui = new CalendarMain();
			return ui;
		});
	}
});