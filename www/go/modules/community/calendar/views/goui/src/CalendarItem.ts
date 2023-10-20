import {BaseEntity, btn, comp, DateTime, Recurrence, t, tbar, win} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {EventDialog} from "./EventDialog.js";

export interface CalendarEvent extends BaseEntity {
	recurrenceRule?: any
	recurrenceOverrides?: any
	links?: any
	alerts?: any
	showWithoutTime?: boolean // isAllDay
	duration: string
	start: string
	title: string
	color?: string
	calendarId: string
}

const eventDS = jmapds('CalendarEvent');

interface CalendarItemConfig {
	key: string // id/recurrenceId
	recurrenceId?:string
	data: Partial<CalendarEvent>
	title?: string
	start: DateTime
	end: DateTime
	color?: string
}

/**
 * This is the ViewModel for items displaying in the calendar.
 * For now, they can be generated from the CalendarEvent model.
 * Because if recurrence (and overrided) 1 CalendarEvent may return multiple items
 */
export class CalendarItem {

	key!: string // id/recurrenceId
	recurrenceId?:string
	data!: CalendarEvent
	title!: string
	start!: DateTime
	end!: DateTime
	color!: string

	divs: {[week: string] :HTMLElement}

	constructor(obj:CalendarItemConfig) {
		Object.assign(this,obj);
		if(!obj.title) {
			this.title = obj.data.title!;
		}
		if(!obj.color) {
			this.color = obj.data.color || '356772';
		}
		this.divs = {};
	}

	static makeItems(e: CalendarEvent, from: DateTime, until: DateTime) : CalendarItem[] {
		const start = new DateTime(e.start),
			end = start.clone().addDuration(e.duration),
			color = e.color || calendarStore.items.find((c:any) => c.id == e.calendarId)?.color,
			items = [];
		if(end.date > from.date && start.date < until.date && !e.recurrenceRule) {
			items.push(new CalendarItem({
				key: e.id+"",
				start,
				end,
				data:e,
				color
			}));
		}
		if(e.recurrenceRule) {
			const r = new Recurrence({dtstart: new Date(e.start), rule: e.recurrenceRule, ff: from.date});
			let rEnd = r.current.clone().addDuration(e.duration);
			while(r.current.date < until.date && rEnd.date > from.date) {
				//if(r.current.date < until.date) {
				//do {
				//const rEnd = r.current.clone().addDuration(e.duration);
				//if(rEnd.date > from.date) {
				const recurrenceId = r.current.format('Y-m-d\Th:i:s');
				if (e.recurrenceOverrides && recurrenceId in e.recurrenceOverrides) {
					const o = e.recurrenceOverrides[recurrenceId];
					if(o.excluded) {
						// excluded
					} else {
						const os = o.start ? new DateTime(o.start) : r.current.clone();
						items.push(new CalendarItem({
							key: e.id + '/' + recurrenceId,
							recurrenceId: recurrenceId,
							start: os,
							title: o.title || e.title,
							end: (o.duration || o.start) ? os.clone().addDuration(o.duration || e.duration) : rEnd,
							data: e,
							color: o.color ?? color
						}));
					}
				} else {
					items.push(new CalendarItem({
						key: e.id + '/' + recurrenceId,
						recurrenceId: recurrenceId,
						start: r.current.clone(),
						end: rEnd,
						data: e,
						color
					}));
				}
				r.next();
				rEnd = r.current.clone().addDuration(e.duration);
				//}
				//} while(r.current.date < until.date && r.next())
			}
		}
		return items;
	}

	remove() {
		if(!this.isRecurring) {
			eventDS.destroy(this.data.id);
		} else {
			const w = win({
					title: t('Do you want to delete a recurring event?'),
					modal: true,
				},comp({
					cls:'pad',
					html: t('You will be deleting a recurring event. Do you want to delete this occurrence only or all future occurrences?')
				}),tbar({},btn({
						text: t('This event'),
						cls:'primary',
						handler: b => { this.removeOccurrence(); w.close(); }
					}),btn({
						text: t('All future events'),
						handler: b => { this.removeFutureEvents(); }
					}),'->',btn({
						text: t('Cancel'), // save to series
						handler: b => w.close()
					})
				)
			)
			w.show();
		}
	}

	get isRecurring() {
		return this.key.includes('/');
	}

	get isOverride() {
		return (this.recurrenceId && this.recurrenceId in this.data.recurrenceOverrides);
	}

	save(onCancel: Function) {
		const start = this.start.format('Y-m-dTH:i:s'),
			duration = this.start.diff(this.end);

		if (start != (this.recurrenceId || this.data.start) || duration != this.data.duration) {
			if(this.data.id) {
				this.patch({start,duration}); // quick save
			} else {
				this.data.start = start;
				this.data.duration = duration;
				this.edit(onCancel); // open dialog
			}
		}
	}

	edit(onCancel?: Function) {
		//if (!ev.data.id) {
		const dlg = new EventDialog();
		dlg.on('close', () => {
			// cancel ?
			onCancel && onCancel();
			// did we save then show loading circle instead
			if(!this.key) // new
				Object.values(this.divs).forEach(d => d.remove());

		})
		dlg.show();
		dlg.load(this);
	}

	downloadIcs(){
		client.downloadBlobId('community/calendar/ics/'+this.key, 'test.ics');
	}

	patch(modified: any, onFinish?: Function) {
		if(!this.isRecurring) {
			eventDS.update(this.data.id, modified); // await?
		} else if(this.isOverride) {
			this.patchOccurrence(modified, onFinish);
		} else {
			const w = win({
					title: t('Do you want to edit a recurring event?'),
					modal: true,
				},comp({
					cls:'pad',
					html: t('You will be editing a recurring event. Do you want to edit this occurrence only or all future occurrences?')
				}),tbar({},btn({
						text: t('This event'),
						cls:'primary',
						handler: b => { this.patchOccurrence(modified, onFinish); w.close(); }
					}),btn({
						text: t('All future events'),
						handler: b => { this.patchThisAndFuture(); }
					}),'->',btn({
						text: t('Cancel'), // save to series
						handler: b => w.close()
					})
				)
			)
			w.show();
		}
	}

	private static overridableProperties = ['start', 'duration', 'title', 'freeBusyStatus', 'participants','location','alerts', 'description']

	private patchOccurrence(modified: any, onFinish?: Function) {
		this.data.recurrenceOverrides ??= {};
		for(const prop in modified) {
			if(!CalendarItem.overridableProperties.includes(prop)) delete modified[prop]; // remove properties that can not be overridden
		}
		let o = Object.assign(
			this.isOverride ? this.data.recurrenceOverrides[this.recurrenceId!] : {},
			modified
		);
		eventDS.single(this.data.id).then(original => {
			if(!original) return; // why could this be undefined?
			for(const name of CalendarItem.overridableProperties) {
				if(o[name] == original[name]) delete o[name]; // remove properties that are the same as original TODO alerts and participants cannot be compared like this
			}
			this.data.recurrenceOverrides[this.recurrenceId!] = o;
			eventDS.update(this.data.id, {recurrenceOverrides:this.data.recurrenceOverrides}).then(onFinish);
		});
	}

	/**
	 * @see  https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-10.html#section-5.5
	 */
	private patchThisAndFuture() {
		alert('todo');
		return;
		//if(!ev.data.participants) { // is not scheduled ( split event)
		// todo: add first and next relation in relatedTo property as per https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-11.html#name-splitting-an-event
		this.data.start = this.recurrenceId!;
		eventDS.create(this.data).then((data) => { // duplicate event
			let rule = this.data.recurrenceRule;
			rule.until = this.start.addSeconds(-1);
			eventDS.update(this.data.id, {recurrenceRule: rule}); // set until on original
		}); // create duplicate
		//} else {
		// todo: find all occurrences and create exceptions that match the original until this one, Then change the original
		//}
	}

	private removeFutureEvents() {
		this.data.recurrenceRule.until = this.recurrenceId;
		eventDS.update(this.data.id,{recurrenceRule: this.data.recurrenceRule});
	}

	private removeOccurrence() {
		eventDS.update(this.data.id, {recurrenceOverrides:{[this.recurrenceId!]:{excluded:true}}});
	}
}