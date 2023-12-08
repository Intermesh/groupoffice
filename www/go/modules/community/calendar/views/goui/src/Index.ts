import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {router} from "@intermesh/groupoffice-core";
import {datasourcestore, t, E, translate, DateTime} from "@intermesh/goui";
import {CalendarEvent} from "./CalendarItem.js";

export const calendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true}},
	//properties: ['id', 'name', 'color', 'isVisible', 'isSubscribed'],
	sort: [{property:'sortOrder'}]
})

function addEmailAction() {

	if(GO.email) {
		function processInvite(msg:any, status?:string){
			client.jmap('community/calendar/CalendarEvent/processInvite', {
				status,
				scheduleId: msg.itip.scheduleId,
				accountId: msg.account_id,
				mailbox: msg.mailbox,
				uid: msg.uid
			}).then(data => {
				console.log(data);
				// if(data.attendance_event_id){
				// 	GO.email.showAttendanceWindow(data.attendance_event_id);
				// }
				//this.loadMessage();
			}).catch(r => {
				if(r.type)
					alert(r.message);
			})
		}
		GO.email.handleITIP = (container: HTMLUListElement, msg:{itip: {method:string, event: CalendarEvent, feedback?:string}} ) => {
			const itip = msg.itip;
			if(itip) {
				const btns = E('div').cls('btns');

				let text = itip.feedback || "This message contains an appointment invitation that was already processed.";

				switch(itip.method) {
					case "REPLY":
						text = t("Reply");
						btns.append(
							E('button', t("Indicate whether you participate in this event")).cls('goui-button').on('click', _ => {processInvite(msg);})
						);
					break;
					case 'CANCEL':
						text = t("Cancellation");
						btns.append(
							E('button', t("Delete Event")).cls('goui-button').on('click', _ => {processInvite(msg);})
						);
					break;
					case 'REQUEST':
						text = t("Invitation"); // ma 4 dec 12:00
						btns.append(
							E('div',
								E('button', t("Accept")).cls('goui-button').on('click', _ => {processInvite(msg,'accepted');}),
								E('button', t("Maybe")).cls('goui-button').on('click', _ => {processInvite(msg,'tentative');}),
								E('button', t("Decline")).cls('goui-button').on('click', _ => {processInvite(msg,'declined');})
							).cls('goui group'),
							E('button', t("Open Calendar")).cls('goui-button').on('click', _ => {
								alert('todo: show day schedule for event start till end time');
							})
						);
					break;
				}
				if(itip.event) {
					text += ' "' + itip.event.title+'" '+t('at', 'community', 'calendar') +' '+ DateTime.createFromFormat(itip.event.start.replace('T', ' '),'Y-m-d H:i')!.format('D j M H:i')
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