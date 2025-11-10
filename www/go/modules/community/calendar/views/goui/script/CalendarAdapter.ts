import {DataSourceStore, datasourcestore, DateTime, DefaultEntity, Observable, t, Window} from "@intermesh/goui";
import {client, JmapDataSource, jmapds, principalDS} from "@intermesh/groupoffice-core";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";

export interface CalendarProvider {
	[key:string]:any
	enabled: boolean
	load(start:DateTime,end:DateTime): Promise<void | DefaultEntity[]>
	items(start:DateTime,end:DateTime): Generator<CalendarItem, void>
}

export interface CalendarAdapterEventMap {
	load: {start:DateTime, end: DateTime}
}

export class CalendarAdapter extends Observable<CalendarAdapterEventMap> {

	private start!: DateTime
	private end!: DateTime

	constructor() {
		super();
		for(const type in this.providers) {
			const p = this.providers[type];
			if(p.watch) {
				p.store.on('load', (me: & {skipNextEvent:boolean}) => {
					if(p.skipWatch) {
						p.skipWatch = false; // skip only once
					} else {
						this.onLoad();
					}
				});
			}
		}
	}

	onLoad = () => {}

	goto(start:DateTime,end:DateTime) {
		this.start = start;
		this.end = end;
		const promises =  [];
		for(const type in this.providers) {
			const p = this.providers[type];
			if(p.enabled) {
				p.skipWatch = true; // will skip extra onLoad call for providers that have a load handler
				promises.push(p.load(start, end));
			}
		}
		Promise.all(promises).then(() => {
			this.onLoad()

			this.fire("load", {start, end});

		}).catch(e => Window.error(e))
	}

	private *generator() {
		for(const type in this.providers) {
			const p = this.providers[type];
			if(!p.enabled) continue;
			for (const item of p.items(this.start, this.end)) {
				item.provider = type;
				yield item;
			}
		}
	}

	get items() {
		return this.generator();
	}

	byType(type: string) {
		return this.providers[type];
	}

	public registerProvider(type:string, provider:CalendarProvider) {
		this.providers[type] = provider;
	}

	private providers: {[type:string] : CalendarProvider} = {
		'event': {
			enabled: true,
			watch:true,
			store:datasourcestore({
				dataSource:jmapds('CalendarEvent'),
				relations: {
					modifier: {dataSource: principalDS, path: "modifiedBy"},
					creator: {dataSource: principalDS, path:'createdBy'}
				}}),
			*items(start:DateTime,end:DateTime) {

				// Sort personal calendar events on top so merged events will favor the personal one over shared items.
				const personalCalendarId = client.user.calendarPreferences?.personalCalendarId;

				const events = (this.store as DataSourceStore<JmapDataSource<CalendarEvent>>).data.sort((a,b) => {
					if(a.calendarId == personalCalendarId) {
						return -1;
					}

					if(b.calendarId == personalCalendarId) {
						return 1;
					}

					return 0;
				});

				for (const e of events) {
					for(const item of CalendarItem.expand(e as CalendarEvent, start, end)) {
						if ((!item.isDeclined || client.user.calendarPreferences.showDeclined!==false) ) {
							yield item;
						}
					}
				}
			},
			load(start:DateTime,end:DateTime) {
				Object.assign(this.store!.queryParams.filter ||= {}, {
					after: start.format('Y-m-d'),
					before: end.format('Y-m-d')
				});
				return this.store!.load();
			}
		},
		'holiday': {
			enabled: client.user.calendarPreferences?.holidaysAreVisible,
			list:[],
			open(){},
			load(start:DateTime,end:DateTime) {
				if(!client.user.holidayset) {
					client.user.holidayset = client.user.language;
				}
				let [lang,country] = client.user.holidayset.split('_');
				if(!country) country = lang;
				if(country=='uk')
					country ='gb';
				return client.jmap("community/calendar/Holiday/fetch",{
					set: country.toUpperCase(), lang: client.user.holidayset.replace("_", "-"),from:start.format('Y-m-d'),till:end.format('Y-m-d')
				}).then(r => {
					this.list = r.list;
				});
			},
			*items(start: DateTime, end: DateTime) {
				for(const o of this.list) {
					const start = DateTime.createFromFormat(o.start,'Y-m-d')!;

					let title  = o.title;
					if(o.region) {
						title += " (" + o.region + ")";
					}
					yield new CalendarItem({
						key: '',
						start,
						extraIcons: ['family_star'],
						defaultColor: '025d7b',
						data: {
							title: title,
							duration: o.duration,
							showWithoutTime: true,
						},
						cal: {
							name: t("Holidays")
						}
					});
				}
			}
		},
		'task': {
			enabled: client.user.calendarPreferences?.tasksAreVisible,
			store: datasourcestore({
				dataSource: jmapds('Task'),
				relations: {
					modifier: {dataSource: principalDS, path: "modifiedBy"},
					creator: {dataSource: principalDS, path:'createdBy'}
				}
			}),
			*items(from:DateTime,until:DateTime) {
				for(const task of this.store!.items) {
					let date;

					if(task.progress == 'completed') {
						date = task.progressUpdated || (new DateTime()).format('Y-m-d'); // slice date
					} else {
						date = task.due || task.start || (new DateTime()).format('Y-m-d');
					}

//if(task.title =='test taak met bogus timezone') debugger;
					const start = DateTime.createFromFormat(date.substring(0,10), 'Y-m-d');

					if(!start) {
						continue;
					}

					if(start.date <= until.date && start.date >= from.date) {
						// console.log(task.progress, date, start, task.title, task);
						task.duration = 'PT1H';
						task.showWithoutTime = true;
						yield new CalendarItem({
							key: '-',
							start,
							open() {
								const dlg = new go.modules.community.tasks.TaskDialog();
								dlg.show();
								dlg.load(task.id);
							},
							extraIcons: [task.progress == 'completed' ? 'task_alt' : 'radio_button_unchecked'],
							defaultColor: '7e472a',
							data: task,
							cal: {
								name: t("Tasks")
							}
						});
					}
				}
			},
			load(start:DateTime,end:DateTime) {
				// this.store!.setFilter("todo", {
				// 	start: start.format('Y-m-d')+'..'+end.format('Y-m-d'),
				// }).setFilter('done', {
				// 	progressUpdated: start.format('Y-m-d')+'..'+end.format('Y-m-d'),
				// 	//progress: 'NOT needs-action OR in-progress'
				// });
				this.store.setFilter('range',{
					operator: "OR",
					conditions: [
						{start:null,due:null},
						{start: start.format('Y-m-d')+'..'+end.format('Y-m-d')},
						{progressUpdated: start.format('Y-m-d')+'..'+end.format('Y-m-d')},
					]
				});
				return this.store!.load();
			}
		},
		'birthday': {
			enabled: client.user.calendarPreferences?.birthdaysAreVisible,
			store: datasourcestore({
				dataSource: jmapds('Contact'),
				relations: {
					modifier: {dataSource: principalDS, path: "modifiedBy"},
					creator: {dataSource: principalDS, path:'createdBy'}
				}
			}),
			*items(from:DateTime,end:DateTime) {
				const sy= from.getYear(),
					ey= end.getYear();
				for(const b of this.store.items) {
					const start = DateTime.createFromFormat(b.birthday,'Y-m-d')!;

					start.setYear(b.birthday.split('-')[1]<7?ey:sy);
					yield new CalendarItem({
						key: "",
						start,
						open() {
							const dlg = new go.modules.community.addressbook.ContactDialog();
							dlg.show();
							dlg.load(b.id);
						},
						extraIcons: ['cake'],
						defaultColor: '009c63',
						data:{
							title: t('{name}\'s birthday').replace('{name}',b.name),
							duration: 'P1D',
							showWithoutTime:true,
						},
						cal: {
							name: t("Birthdays")
						}
					});
				}
			},
			load(start:DateTime,end:DateTime) {
				start = start.clone().addDays(1);// birthday filter end date in inclusive
				this.store! //.setFilter('addressBookIds', {addressBookIds: go.User.birthdayPortletAddressBooks})
					.setFilter('isOrganisation', {isOrganization: false})
					.setFilter('birthday', {birthday: start.format('Y-m-d')+'..'+end.format('Y-m-d')})
				return this.store!.load();
			}
		}
	}
}