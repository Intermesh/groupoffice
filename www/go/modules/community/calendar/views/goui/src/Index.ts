import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {router} from "@intermesh/groupoffice-core";
import {datasourcestore, t, E, translate, DateTime} from "@intermesh/goui";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";

export const calendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true}},
	//properties: ['id', 'name', 'color', 'isVisible', 'isSubscribed'],
	sort: [{property:'sortOrder'}]
})

function addEmailAction() {

	if(GO.email) {

		GO.email.handleITIP = (container: HTMLUListElement, msg:{itip: {method:string, event: CalendarEvent|string, feedback?:string}} ) => {
			if(msg.itip) {
				const event = msg.itip.event,
					btns = E('div').cls('btns');

				let text = msg.itip.feedback || {
					CANCEL: t("Cancellation"),
					REQUEST: t("Invitation")
				}[msg.itip.method] || "Unable to process appointment information.";

				if(msg.itip.method === 'REQUEST' && typeof event !== 'string') {
					const item = new CalendarItem({data:event, key:event.id});
					btns.append(
						E('div',
							E('button', t("Accept")).cls('goui-button').on('click', _ => {item.updateParticipation('accepted');}),
							E('button', t("Maybe")).cls('goui-button').on('click', _ => {item.updateParticipation('tentative');}),
							E('button', t("Decline")).cls('goui-button').on('click', _ => {item.updateParticipation('declined');})
						).cls('goui group'),
						E('button', t("Open Calendar")).cls('goui-button').on('click', _ => {
							alert('todo: show day schedule for event start till end time');
						})
					);
				}

				if(event) {
					if(typeof event === "string") {
						text += ', '+ event;
					} else if(msg.itip.method !== 'REPLY') {
						text += ' "' + event.title + '" ' + t('at', 'community', 'calendar') + ' ' + DateTime.createFromFormat(event.start.replace('T', ' '), 'Y-m-d H:i')!.format('D j M H:i')
					}
				}

				container.append(
					E('li', E('i', 'event').cls('icon'), text, btns).cls('goui-toolbar')
				);
			}
		};

	}
}

modules.register(  {
	package: "community",
	name: "calendar",
	init () {

		translate.load(GO.lang.community.calendar, "community", "calendar");

		addEmailAction();

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