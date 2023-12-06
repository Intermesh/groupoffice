import {jmapds, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {router} from "@intermesh/groupoffice-core";
import {datasourcestore, t, translate} from "@intermesh/goui";

export const calendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true}},
	//properties: ['id', 'name', 'color', 'isVisible', 'isSubscribed'],
	sort: [{property:'sortOrder'}]
})

modules.register(  {
	package: "community",
	name: "calendar",
	init () {

		translate.load(GO.lang.community.calendar, "community", "calendar");

		modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => {
			let ui = new Main();

			router.add(/^calendar\/year\/(\d+)$/, (year) => {
				modules.openMainPanel("calendar");
				//ui.data.view = 'year';
				ui.cards.activeItem = -1; //'year');
			}).add(/^calendar\/month\/(\d+)$/, (year, month) => {
				modules.openMainPanel("calendar");
				//ui.data.view = 'month';
				ui.cards.activeItem = 1; // month
			}).add(/^calendar\/week\/(\d+)$/, (year, week) => {
				modules.openMainPanel("calendar");
				//ui.data.view = 'week';
				ui.cards.activeItem = 0; // week
			});

			return ui;
		});
	}
});