import {AclLevel, client, jmapds, main, modules, principalDS, settingsPanels} from "@intermesh/groupoffice-core";
import {Main} from "./Main.js";
import {datasourcestore, DateTime, E, router, t as coreT, translate, Window} from "@intermesh/goui";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {EventDetail, EventDetailWindow} from "./EventDetail.js";
import {PreferencesPanel} from "./PreferencesPanel";
import {EventWindow} from "./EventWindow";
import {CalendarAdapter} from "./CalendarAdapter";

export * from "./Main.js";
export * from "./CalendarList.js";
export * from "./CalendarView.js";
export * from "./CalendarItem.js";
export * from "./MonthView.js";
export * from "./SplitView.js";
export * from "./WeekView.js";
export * from "./CalendarAdapter.js";
export * from "./OnlineMeetingService.js";

translate.load(GO.lang.core.core, "core", "core");
translate.load(GO.lang.community.calendar, "community", "calendar");

export type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month' | 'year' | 'split' | 'list' | 'custom';
export const calendarStore = datasourcestore({
	dataSource: jmapds<any>('Calendar'),
	queryParams:{filter:{isSubscribed: true, davaccountId : null}},
	sort: [{property:'groupId'},{property:'sortOrder'},{property:'name'}],
	relations: {
		owner: {
			path: "ownerId",
			dataSource: principalDS
		},
		group: {
			path: "groupId",
			dataSource:jmapds('ResourceGroup')
		}
	}
});

export const writeableCalendarStore = datasourcestore({
	dataSource:jmapds('Calendar'),
	queryParams:{filter:{isSubscribed: true, davaccountId : null, permissionLevel:30/*writeOwn*/}},
	sort: [{property:'sortOrder'},{property:'name'}],
	relations: {
		owner: {
			path: "ownerId",
			dataSource: principalDS
		}
	}
})

export const categoryStore = datasourcestore({
	dataSource:jmapds('CalendarCategory'),
	sort: [{property:'name'}]
})

export const viewStore = datasourcestore({
	dataSource:jmapds('CalendarView'),
	sort: [{property:'name'}]
})

export const adapter = new CalendarAdapter();

export const t = (key:string,p='community',m='calendar') => coreT(key, p,m);
export const statusIcons = {
	'accepted':		['check_circle', t('Accepted'), 'green'],
	'tentative':	['help', t('Maybe'), 'orange'],
	'declined':		['block', t('Declined'), 'red'],
	'needs-action':['schedule', t('Awaiting reply'), 'orange']
} as {[status:string]: string[]}

export function getParticipantStatusIcon(p:any): string[] {
	// error sending mail
	if(p.scheduleStatus) {
		if(p.scheduleStatus == "1.0") {
			return ['pending', t("Invite not send yet"), 'orange'];
		}

		if(p.scheduleStatus.substring(0, 1) != "1") {
			return ['error', p.scheduleStatus, 'danger'];
		}
	}

	return statusIcons[p.participationStatus] ? statusIcons[p.participationStatus] : statusIcons["needs-action"];
}

function addEmailAction() {
	if(window.go) {
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
						go.openEventWindow(response.data);
					}
				}
			});
		}

		go.openEventWindow = (data:any, editable: boolean) => {
			const dlg = editable ? new EventWindow() : new EventDetailWindow();
			data.start = data.start ?? (new DateTime).format('Y-m-d\TH:00:00.000');
			data.showWithoutTime = data.showWithoutTime ?? client.user.calendarPreferences?.defaultDuration == null;
				data.duration = data.duration ?? client.user.calendarPreferences?.defaultDuration ?? "P1D";
				data.calendarId = data.calendarId ?? client.user.calendarPreferences?.defaultCalendarId;
			dlg.loadEvent(new CalendarItem({data:data, key: data.id ? data.id + "" : null}));
			dlg.show();

			return dlg;
		};


		if(GO.email) GO.email.handleITIP = async (container: HTMLUListElement, msg:{itip: {method:string, event: CalendarEvent|string, feedback?:string, recurrenceId?:string}} ) => {
			if(msg.itip) {
				if(!calendarStore.loaded) {
					// CalendarItem depends on the store being loaded
					await calendarStore.load();
				}
				const event = msg.itip.event,
					btns = E('div').cls('btns'),
					names: any = {accepted: t("Accept"), tentative: t("Maybe"), declined: t("Decline")},
					pressedNames: any = {accepted: t("Accepted"), tentative: t("Maybe"), declined: t("Declined")},
					updateBtns = (item: CalendarItem) => {

						btns.innerHTML = '';
						if(!item.calendarPrincipal){
							btns.append(
								E('button', t("Import")).cls('goui-button').on('click', _ => {
									item.save();
								}));
						} else {
							btns.append(
								E('div',
									...['accepted', 'tentative', 'declined'].map(s => E('button', item.calendarPrincipal?.participationStatus == s ? pressedNames[s] : names[s])
										.cls('goui-button')
										.cls('disabled', item.calendarPrincipal?.participationStatus == s)
										.cls('pressed', item.calendarPrincipal?.participationStatus == s)
										.on('click', _ => {
											item.updateParticipation(s as 'accepted'|'declined'|'tentative',() => {
												updateBtns(item);
											});
										})
									)
								).cls('goui group')

							);
						}
					};
				let text = {
					CANCEL: t("Cancellation"),
					REQUEST: t("Invitation"),
					NONE: t('Event'),
					REPLY: t('Reply'),
				}[msg.itip.method] || "Unable to process appointment information.";


				if(event && typeof event !== 'string') {
					let item = new CalendarItem({data:event, key:event.id ? event.id + "" : ''});

					if(msg.itip.recurrenceId && item.isRecurring) {
						item = item.patchedInstance(msg.itip.recurrenceId);
					}
					if(msg.itip.method === 'REQUEST' || msg.itip.method === 'NONE')
						updateBtns(item);

					if(item.start) {
						btns.append(E('button', t("Open Calendar")).cls('goui-button').on('click', _ => {
							router.goto("calendar/day/" + item.start.format('Y-m-d'));
						}));
					}

					if(msg.itip.method !== 'REPLY') {

						const date = item.start;

						text += ' "' + item.title + '" ' + t('at') + ' ' + date.format('D j M H:i')
					}

				} else {
					text += ', '+ (event ?? t('Unexisting event'));
				}

				const items = [text];

				if(msg.itip.feedback) {
					items.push(msg.itip.method != "REPLY" ? E("br") : ": ", msg.itip.feedback);
				}

				container.append(
					E('li', E('i', 'event').cls('icon'), E("div",  ...items).css({flex: "1"}), btns).cls('goui-toolbar')
				);
			}
		};

	}
}

export const mainPanel: {ui?:Main} = {};

modules.register(  {
	package: "community",
	name: "calendar",
	entities: [
		{
			name:"Calendar",
			permissions:[
				{value: 5, name: t("Read free/busy")},
				{value: 10,name: t("Read items")},
				{value: 20,name: t("Update private")},
				{value: 25,name: t("RSVP")},
				{value: 30,name: t("Write own")},
				{value: 35,name: t("Write all")},
				{value: 40,name: t("Delete")},
				{value: 50,name: t("Manage")}
			]
		},
		{
			name: "CalendarView",
			permissions: [
				{value: 10,name: t("Read")},
				{value: 30,name: t("Write")},
				{value: 50,name: t("Manage")}
			]
		},
		"CalendarCategory",
		{
			name: "ResourceGroup"
		},
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
									permissionLevel: AclLevel.WRITE
								}
							}
						}
					}, multiple: true, title: t("Calendars")}
			],
			links: [{
				iconCls: 'entity ic-event red',
				linkWindow: async (entity:string, entityId) => {
					if(!calendarStore.loaded) {
						// CalendarItem depends on the store being loaded
						await calendarStore.load();
					}

					const dlg = go.openEventWindow({
							start:(new DateTime).addDays(1).format('Y-m-d\TH:00:00.000'),
							title: t('New event'),
							showWithoutTime: client.user.calendarPreferences?.defaultDuration == null,
							duration: client.user.calendarPreferences?.defaultDuration ?? "P1D",
							calendarId: client.user.calendarPreferences?.defaultCalendarId
						}, true);

					if(entity == "Contact") {
						try {
							const p = await principalDS.single("Contact:" + entityId);
							dlg.participantFld.addParticipant(p);
						} catch(e) {
							console.error(e);
						}
					}

					return dlg;
				},
				linkDetail:() =>  new EventDetail(),
				linkDetailCards () {

					var forth = new go.links.DetailPanel({
						link: {
							title: t("Upcoming appointments"),
							iconCls: 'icon ic-event orange',
							entity: "CalendarEvent",
							filter: null
						}
					});

					forth.store.setFilter('date', {eventsAfter: true});

					var past = new go.links.DetailPanel({
						link: {
							title: t("Past appointments"),
							iconCls: 'icon ic-event orange',
							entity: "CalendarEvent",
							filter: null
						}
					});

					past.store.setFilter('past', {eventsBefore: true});

					return [forth, past];
				}
			}]
		}
	],

	panels: {
		calendar: {
			cmp: Main,
			title: t("Calendar"),
			routes: {
				'^calendar/(month|list|week|day|year)/(\\d{4}-\\d{2}-\\d{2})$'(span, ymd){
					this.show();
					this.goto(new DateTime(ymd)).setSpan(span as ValidTimeSpan, 0);
				},
				'^calendar/(days|weeks|split|custom)-(\\d+)/(\\d{4}-\\d{2}-\\d{2})$'(span, amount, ymd) {
					this.show();
					this.goto(new DateTime(ymd)).setSpan(span as ValidTimeSpan, Math.min(parseInt(amount),373));
				},
				async '^calendarevent/(\d+)$' (id)  {
					// for notification clicks
					this.show();
					const event = await jmapds('CalendarEvent').single(id);
					if(event)
						(new CalendarItem({data: event, key: id})).open();

				}
			}
		}

	},

	userSettingsPanels: [PreferencesPanel],

	init () {
		//const user = client.user;


		addEmailAction();

		client.on("authenticated",  ({session}) => {


			client.user.calendarPreferences ||= {};
			if(!session.capabilities["go:community:calendar"]) {
				return; // User has no access to this module
			}

			// // OLD CODE
			// async function showBadge() {
			// 	const count = await go.Jmap.request({method: "CalendarEvent/countMine"});
			// 	mainPanel.ui!.inboxBtn.hidden = count<1;
			// 	GO.mainLayout.setNotification('calendar', count, 'orange');
			// }
			// go.Db.store("CalendarEvent").on("changes", () => {
			// 	showBadge();
			// });
			// showBadge();
			// // END OLD CODE


			// TODO: Move to entity register
			main.notifier.regRenderer('CalendarEvent', (alert, closeFn) => {
				const entity = alert.entityData,
					msgs: {[key:string]: string} = {
						request: t('New invitation from {from}'),
						reply: t("Invitation updated by {from}"),
						created: t("New event created by {creator}")
					};
				let text= msgs[alert.tag] || go.util.Format.shortDateTime(alert.recurrenceId || entity.start, true);
				const actions: any = {};
				if(alert.tag === 'created')
					text = text.replace('{creator}', alert.data.creator);

				if(alert.tag === 'reply' || alert.tag === 'request')
					text = text.replace('{from}', alert.data.from?.personal ?? t("Unknown"));

				if(alert.tag === 'request') {
					const item = new CalendarItem({key: alert.entityId + "", data: entity});
					actions.primary = {run:() => { item.updateParticipation("accepted", () => closeFn()); }};
					actions.secondary =  {run:() => { item.updateParticipation("declined", () => closeFn()); } };
				}
				return {
					title: entity.title,
					text,
					icon: {name:'event', color: 'red'},
					category: 'event',
					actions
				};
			});

		});

	}
});