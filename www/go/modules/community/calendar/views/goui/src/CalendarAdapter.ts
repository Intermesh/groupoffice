import {DataSourceStore, datasourcestore, DateTime, DefaultEntity} from "@intermesh/goui";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";

interface CalendarProvider {
	[key:string]:any
	enabled: boolean
	load(start:DateTime,end:DateTime): Promise<void | DefaultEntity[]>
	items(start:DateTime,end:DateTime): Generator<CalendarItem, void>
}
export class CalendarAdapter {

	private start!: DateTime
	private end!: DateTime

	onLoad = () => {}

	goto(start:DateTime,end:DateTime) {
		this.start = start;
		this.end = end;
		const promises = [];
		for(const type in this.providers) {
			const p = this.providers[type];
			if(!p.enabled) continue;
			promises.push(p.load(start,end));
		}
		Promise.all(promises).then(this.onLoad);
	}

	private *generator() {
		for(const type in this.providers) {
			const p = this.providers[type];
			if(!p.enabled) continue;
			for (const item of p.items(this.start, this.end)) {
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

	private providers: {[type:string] : CalendarProvider} = {
		'event': {
			enabled: true,
			store:datasourcestore({dataSource:jmapds('CalendarEvent'), listeners:{'load':()=>this.onLoad()}}),
			*items(start:DateTime,end:DateTime) {
				for (const e of this.store!.items) {
					for(const item of CalendarItem.expand(e as CalendarEvent, start, end))
						yield item;
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
			enabled: true,
			list:[],
			load(start:DateTime,end:DateTime) {
				return client.jmap("community/calendar/Holiday/fetch",{
					set: 'NL', lang: 'nl',from:start.format('Y-m-d'),till:end.format('Y-m-d')
				}).then(r => {
					this.list = r.list;
				});
			},
			*items(start: DateTime, end: DateTime) {
				for(const o of this.list) {
					const start = DateTime.createFromFormat(o.start,'Y-m-d')!;
					yield new CalendarItem({
						key: '',
						start,
						data: {
							title: o.title,
							color: '00dd00',
							duration: o.duration,
							showWithoutTime: true,
						}
					});
				}
			}
		},
		'task': {
			enabled: true,
			store: datasourcestore({dataSource: jmapds('Task')}),
			*items(start:DateTime,end:DateTime) {
				for(const t of this.store!.items) {
					const start = DateTime.createFromFormat(t.start, 'Y-m-d');
					yield new CalendarItem({
						key: t.id,
						start,
						extraIcons:['task_alt'],
						data: {
							title: t.title,
							color: '0000ff',
							duration: 'P1D',
							showWithoutTime: true,
						}
					});
				}
			},
			load(start:DateTime,end:DateTime) {
				this.store!.setFilter("todo", {
					start: start.format('Y-m-d')+'..'+end.format('Y-m-d'),
				}).setFilter('done', {
					progressUpdated: start.format('Y-m-d')+'..'+end.format('Y-m-d'),
					//progress: 'NOT needs-action OR in-progress'
				});
				return this.store!.load();
			}
		},
		'birthday': {
			enabled: true,
			store: datasourcestore({dataSource: jmapds('Contact')}),
			*items(from:DateTime,end:DateTime) {
				const sy= from.getYear();
				for(const b of this.store.items) {
					const start = DateTime.createFromFormat(b.birthday,'Y-m-d')!;
					start.setYear(sy);
					yield new CalendarItem({
						key: "",
						start,
						open() {
							const dlg = new go.modules.community.addressbook.ContactDetail();
							dlg.open();
						},
						extraIcons: ['cake'],
						data:{
							title: b.name+'\'s Birthday',
							color: 'ff0000',
							duration: 'P1D',
							showWithoutTime:true,
						}
					});
				}
			},
			load(start:DateTime,end:DateTime) {
				this.store! //.setFilter('addressBookIds', {addressBookIds: go.User.birthdayPortletAddressBooks})
					.setFilter('isOrganisation', {isOrganization: false})
					.setFilter('birthday', {birthday: start.format('Y-m-d')+'..'+end.format('Y-m-d')})
				return this.store!.load();
			}
		}
	}
}