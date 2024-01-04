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
	async init () {

		translate.load(GO.lang.community.calendar, "community", "calendar");

		addEmailAction();

		client.on("authenticated",  (client, session) => {

			if(!session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}

			const ui = new Main();
			router.add(/^calendar\/year\/(\d{4}-\d{2}-\d{2})$/, (year) => {
				modules.openMainPanel("calendar");
				ui.goto(new DateTime(year)).setSpan("year", 365);
			}).add(/^calendar\/month\/(\d{4}-\d{2}-\d{2})$/, (yearMonth) => {
				modules.openMainPanel("calendar");
				ui.goto(new DateTime(yearMonth)).setSpan("month", 31);
			}).add(/^calendar\/week\/(\d{4}-\d{2}-\d{2})$/, (date) => {
				modules.openMainPanel("calendar");
				ui.goto(new DateTime(date)).setSpan("week", 7);
			}).add(/^calendar\/day\/(\d{4}-\d{2}-\d{2})$/, (date) => {
				modules.openMainPanel("calendar");
				ui.goto(new DateTime(date)).setSpan("day", 1);
			}).add(/^calendar\/(days|weeks)-(\d+)\/(\d{4}-\d{2}-\d{2})$/, (span, amount, date) => {
				modules.openMainPanel("calendar");
				ui.goto(new DateTime(date)).setSpan(span as 'days'|'weeks', Math.min(parseInt(amount),373));
			});

			modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => {
				return ui;
			});
		});
	}
});