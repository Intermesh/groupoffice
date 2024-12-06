import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {router} from "@intermesh/groupoffice-core";
import {datasourcestore, t as coreT, E, translate, DateTime, Window, h3} from "@intermesh/goui";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {EventDetail, EventDetailWindow} from "./EventDetail.js";
import {PreferencesPanel} from "./PreferencesPanel";

export * from "./Main.js";

export type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month' | 'year' | 'split' | 'list';
export const calendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true}},
	sort: [{property:'sortOrder'},{property:'name'}]
});

export const categoryStore = datasourcestore({
	dataSource:jmapds('CalendarCategory'),
	sort: [{property:'name'}]
})

export const t = (key:string,p='community',m='calendar') => coreT(key, p,m);
export const statusIcons = {
	'accepted':		['check_circle', t('Accepted'), 'green'],
	'tentative':	['help', t('Maybe'), 'orange'],
	'declined':		['block', t('Declined'), 'red'],
	'needs-action':['schedule', t('Awaiting reply'), 'orange']
} as {[status:string]: string[]}

function addEmailAction() {
	if(go) {
		go.openIcs = (p: any) => {
			const params = p.id ? {
				fileId:p.id
			} : {
				account_id: p.accountId,
				mailbox: p.mailbox,
				uid: p.uid,
				number: p.partId,
				encoding: p.encoding
			}
			go.Jmap.request({
				method: "CalendarEvent/loadICS",
				params,
				callback (options:any, success:boolean, response:any) {
					if (!success) {
						Window.alert(response.errors.join("<br />"), t("Error"));
					} else {
						const dlg = new EventDetailWindow();

						dlg.loadEvent(new CalendarItem({data:response.data, key: response.data.id ? response.data.id + "" : null}));
						dlg.show();
					}
				}
			});
		}

		GO.email.handleITIP = (container: HTMLUListElement, msg:{itip: {method:string, event: CalendarEvent|string, feedback?:string, recurrenceId?:string}} ) => {
			if(msg.itip) {
				const event = msg.itip.event,
					btns = E('div').cls('btns'),
					names: any = {accepted: t("Accept"), tentative: t("Maybe"), declined: t("Decline")},
					updateBtns = (item: CalendarItem) => {

						btns.innerHTML = '';
						if(!item.calendarPrincipal){
							btns.append(t('You are not an invited to this event'));
						} else {
							btns.append(
								E('div',
									...['accepted', 'tentative', 'declined'].map(s => E('button', names[s])
										.cls('goui-button')
										.cls('pressed', item.calendarPrincipal?.participationStatus == s)
										.on('click', _ => {
											item.updateParticipation(s as 'accepted'|'declined'|'tentative',() => {
												updateBtns(item);
											});
										})
									)
								).cls('goui group'),
								E('button', t("Open Calendar")).cls('goui-button').on('click', _ => {
									router.goto("calendar/day/" + item.start.format('Y-m-d'));
								})
							);
						}
					};
				let text = msg.itip.feedback || {
					CANCEL: t("Cancellation"),
					REQUEST: t("Invitation")
				}[msg.itip.method] || "Unable to process appointment information.";


				if(typeof event !== 'string') {
					let item = new CalendarItem({data:event, key:event.id + ""});

					if(msg.itip.recurrenceId && item.isRecurring) {
						item = item.patchedInstance(msg.itip.recurrenceId);
					}
					if(msg.itip.method === 'REQUEST')
						updateBtns(item);

					if(msg.itip.method !== 'REPLY') {

						const date = item.start;

						text += ' "' + item.title + '" ' + t('at') + ' ' + date.format('D j M H:i')
					}

				} else {
					text += ', '+ event;
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
				iconCls: 'entity ic-event red',
				linkWindow:(entity:string, entityId) => {
					return (new CalendarItem({key:'',data:{
							start:(new DateTime).format('Y-m-d\TH:00:00.000'),
							title: t('New event'),
							showWithoutTime: client.user.calendarPreferences?.defaultDuration == null,
							duration: client.user.calendarPreferences?.defaultDuration ?? "P1D",
							calendarId: client.user.calendarPreferences?.defaultCalendarId
						}})).open()
				},
				linkDetail:() =>  new EventDetail()
			}]
		}
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.calendar, "community", "calendar");

		addEmailAction();

		client.on("authenticated",  (client, session) => {

			// OLD CODE
			async function showBadge() {
				const count = await go.Jmap.request({method: "CalendarEvent/countMine"});
				GO.mainLayout.setNotification('calendar', count, 'orange');
			}
			go.Db.store("CalendarEvent").on("changes", () => {
				showBadge();
			});
			showBadge();
			// END OLD CODE
			client.user.calendarPreferences ||= {};
			if(!session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}
			const ui = new Main(),
				nav = (span:ValidTimeSpan, amount: number, ymd?: string) => {
					modules.openMainPanel("calendar");
					ui.goto(new DateTime(ymd)).setSpan(span, amount);
				};
			router.add(/^calendar\/(month|list|week|day|year)\/(\d{4}-\d{2}-\d{2})$/, (span, ymd) => {
					nav(span as ValidTimeSpan, 0, ymd);
				})
				.add(/^calendar\/(days|weeks|split)-(\d+)\/(\d{4}-\d{2}-\d{2})$/, (span, amount, ymd) => {
					nav(span as ValidTimeSpan, Math.min(parseInt(amount),373), ymd); // it fits on my machine
				}).add(/^calendarevent\/(\d+)$/, async (id) => {
					// for notification clicks
					const event = await jmapds('CalendarEvent').single(id);
					if(event)
						(new CalendarItem({data: event, key: id})).open();

				});

			modules.addMainPanel("community", "Calendar", 'calendar', t('Calendar'), () => ui);

			go.Alerts.on("beforeshow", function(alerts: any, alertConfig: any) {
				const alert = alertConfig.alert,
					msgs: {[key:string]: string} = {
						request: t('New invitation from {organizer}'),
						update: t("Invitation updated by {organizer}"),
						created: t("New event created by {creator}")
					};
				//debugger;
				if(alert.entity == "CalendarEvent" || alert.entity == "Calendar") {

					alertConfig.panelPromise = alertConfig.panelPromise.then(async (panelCfg: any) => {

						const msg: string = msgs[alert.tag] || '',
							time = go.util.Format.shortDateTime(alert.triggerAt);

						if(alert.tag === 'created'){
							msg.replace('{creator}', alert.data.creator);
						}
						if(alert.tag === 'update' || alert.tag === 'request') {
							msg.replace('{organizer}', alert.data.organizer);
							panelCfg.buttons = [
								{text:t('Accept')},
								{text:t('Maybe')},
								{text:t('Decline')}
							];
						}

						panelCfg.items = [{html: msg+'<br>'+time }];
						panelCfg.notificationBody = msg+"\n"+time; // for desktop notifications (no html)
						return panelCfg;
					});

				}
			});

		});

		modules.addAccountSettingsPanel("community", "calendar", "calendar", t("Calendar"), "today", () => {
			return new PreferencesPanel();
		});
	}
});