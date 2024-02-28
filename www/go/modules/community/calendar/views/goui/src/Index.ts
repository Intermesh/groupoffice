import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {router} from "@intermesh/groupoffice-core";
import {datasourcestore, t, E, translate, DateTime} from "@intermesh/goui";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {EventWindow} from "./EventWindow.js";
import {EventDetail} from "./EventDetail.js";

export type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month' | 'year' | 'split' | 'list';
export const calendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true}},
	sort: [{property:'sortOrder'}]
});

export const categoryStore = datasourcestore({
	dataSource:jmapds('CalendarCategory'),
	sort: [{property:'name'}]
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
					const item = new CalendarItem({data:event, key:event.id!});
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
	entities: [
		"Calendar",
		{
			name:"CalendarEvent",
			filters: [
				{name: 'text', type: "string", multiple: false, title: t("Query")},
				{name: 'calendarId', type: 'go.form.ComboBox', typeConfig: {
						fieldLabel: t("Calendar"),
						hiddenName: 'calendarId',
						anchor: '100%',
						emptyText: t("Please select..."),
						pageSize: 50,
						valueField: 'id',
						displayField: 'name',
						triggerAction: 'all',
						editable: true,
						selectOnFocus: true,
						forceSelection: true,
						store: {
							xtype: "gostore",
							fields: ['id', 'name'],
							entityStore: "Calendar",
							filters: {
								default: {
									permissionLevel: go.permissionLevels.write
								}
							}
						}
					}, multiple: true, title: t("Calendars")}
			],
			links: [{
				iconCls: 'entity ic-event yellow',
				linkWindow:(entity:string, entityId) => new EventWindow(),
				linkDetail:() =>  new EventDetail()
			}]
		}
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.calendar, "community", "calendar");

		addEmailAction();

		client.on("authenticated",  (client, session) => {

			if(!session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}
			const ui = new Main(),
				nav = (span:ValidTimeSpan, amount: number, ymd?: string) => {
					modules.openMainPanel("calendar");
					ui.goto(new DateTime(ymd)).setSpan(span, amount);
				};
			router.add(/^calendar$/, () => {
					nav('month', 0); // client.user.calendarPreferences.startView ||
				}) // default
				.add(/^calendar\/(month|list|week|day|year)\/(\d{4}-\d{2}-\d{2})$/, (span, ymd) => {
					nav(span as ValidTimeSpan, 0, ymd);
				})
				.add(/^calendar\/(days|weeks|split)-(\d+)\/(\d{4}-\d{2}-\d{2})$/, (span, amount, ymd) => {
					nav(span as ValidTimeSpan, Math.min(parseInt(amount),373), ymd); // it fits on my machine
				});

			modules.addMainPanel("calendar", "Calendar", 'calendar', t('Calendar'), () => {
				return ui;
			});
		});
	}
});