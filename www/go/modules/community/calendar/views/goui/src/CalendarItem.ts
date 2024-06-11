import {
	BaseEntity,
	btn,
	comp,
	DateInterval,
	DateTime,
	E, EntityID, MaterialIcon,
	tbar, Timezone,
	win
} from "@intermesh/goui";
import {calendarStore, t} from "./Index.js";
import {client, jmapds, Recurrence} from "@intermesh/groupoffice-core";
import {EventWindow} from "./EventWindow.js";
import {EventDetailWindow} from "./EventDetail.js";

export interface CalendarEvent extends BaseEntity {
	id: EntityID
	recurrenceRule?: any
	recurrenceOverrides?: {[recurrenceId:string]: (Partial<CalendarEvent> & {excluded?:boolean})}
	links?: any
	alerts?: any
	showWithoutTime?: boolean // isAllDay
	duration: string
	start: string
	timeZone:Timezone
	title: string
	color?: string
	status?: string
	isOrigin: boolean
	participants?: {[key:string]: any}
	calendarId: string
}

const eventDS = jmapds('CalendarEvent');

interface CalendarItemConfig {
	key: string // id/recurrenceId
	recurrenceId?:string
	extraIcons?: MaterialIcon[]
	data: Partial<CalendarEvent>
	override?: Partial<CalendarEvent>
	title?: string
	start?: DateTime
	end?: DateTime
	open?:() => void
	//color?: string
}

/**
 * This is the ViewModel for items displaying in the calendar.
 * For now, they can be generated from the CalendarEvent model.
 * Because if recurrence (and overridden) 1 CalendarEvent may return multiple items
 */
export class CalendarItem {

	key!: string // id/recurrenceId
	recurrenceId?:string
	data!: CalendarEvent
	override?: Partial<CalendarEvent>
	title!: string
	start!: DateTime
	end!: DateTime
	readonly extraIcons;
	//color!: string

	readonly initStart: string
	readonly initEnd: string

	cal: any

	divs: {[week: string] :HTMLElement}

	constructor(obj:CalendarItemConfig) {
		Object.assign(this,obj);
		if(!obj.start) {
			this.start = new DateTime(obj.data.start);
		}
		if(obj.data.timeZone) {
			this.start.timezone = obj.data.timeZone;
			this.start = this.start.toTimezone(client.user.timezone as Timezone);
		}
		if(!obj.end) {
			this.end = this.start.clone().add(new DateInterval(obj.data.duration!));
		}
		this.cal = calendarStore.items.find((c:any) => c.id == obj.data.calendarId);

		this.initStart = this.start.format('Y-m-d\TH:i:s');
		this.initEnd = this.end.format('Y-m-d\TH:i:s');

		if(!obj.title) {
			this.title = obj.data.title!;
		}
		this.extraIcons = obj.extraIcons || [];
		this.divs = {};
	}

	private isNew() {
		return this.key==='';
	}

	private isTimeModified() {
		return this.isNew() || this.initStart !== this.start.format('Y-m-d\TH:i:s') || this.initEnd !== this.end.format('Y-m-d\TH:i:s');
	}

	static *expand(e: CalendarEvent, from: DateTime, until: DateTime) : Generator<CalendarItem> {
		const start = new DateTime(e.start),
			end = start.clone().add(new DateInterval(e.duration));

		if(e.recurrenceRule) {

			const r = new Recurrence({dtstart: new Date(e.start), timeZone:e.timeZone, rule: e.recurrenceRule});
			for(const date of r.loop(from, until)){
				const recurrenceId = date.format('Y-m-d\TH:i:s');

				if (e.recurrenceOverrides && recurrenceId in e.recurrenceOverrides) {
					const o = e.recurrenceOverrides[recurrenceId];
					if(o.excluded) {
						// excluded
					} else {
						const overideStart = o.start ? new DateTime(o.start+(e.showWithoutTime?' 00:00:00.000':'')) : date.clone();
						yield new CalendarItem({
							key: e.id + '/' + recurrenceId,
							recurrenceId,
							start: overideStart,
							title: o.title || e.title,
							end: overideStart.clone().add(new DateInterval(o.duration || e.duration)),
							data: e,
							override: o,
						});
					}
				} else {
					yield new CalendarItem({
						key: e.id + '/' + recurrenceId,
						recurrenceId,
						start: date.clone(),
						end: date.clone().add(new DateInterval(e.duration)),
						data: e
					});

				}
			}
		} else if (end.date > from.date && start.date < until.date) {
			yield new CalendarItem({
				key: e.id+"",
				start,
				end,
				data:e
			});
		}
	}

	get isRecurring() {
		return this.key.includes('/') || (!this.key && 'recurrenceRule' in this.data);
	}

	get isOverride() {
		return (this.recurrenceId && this.data.recurrenceOverrides && this.recurrenceId in this.data.recurrenceOverrides);
	}

	get isDeclined() {
		return this.calendarPrincipal?.participationStatus === 'declined';
	}

	get isCancelled() {
		return (this.override?.status || this.data.status) === 'cancelled';
	}

	get isTentative() {
		return  this.calendarPrincipal?.participationStatus === 'tentative';
	}

	get needsAction() {
		return this.calendarPrincipal?.participationStatus === 'needs-action' && !this.isOwner;
	}

	get color() {
		return this.data.color || this.cal?.color || '356772';
	}

	get participants() {
		return this.override?.participants || this.data.participants;
	}

	get owner() {
		for(const p in this.participants) {
			if (this.participants[p].roles.owner) {
				return this.participants[p];
			}
		}
	}

	/** amount of days this event is spanning */
	get dayLength() {
		// 1 day + the distance in days between start and end. - 1 second of end = 00:00:00
		console.log(this.title, this.start.diff(this.end.clone().addSeconds(-1)));
		return 1 + this.start.diff(this.end.clone().addSeconds(-1)).getTotalDays()!;
	}

	get icons() {
		const e = this.data;
		const icons = [...this.extraIcons];
		if(e.recurrenceRule) icons.push('refresh');
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');
		if(this.isTentative) icons.push('question_mark');
		if(!!e.participants) icons.push('group');

		return icons.map(i=>E('i',i).cls('icon'));
	}

	save(onCancel?: () => void) {
		const f = this.data.showWithoutTime ? 'Y-m-d' : 'Y-m-dTH:i:s';
		const start = this.start.format(f),
			duration = this.start.diff(this.end).toIso8601();

		if (this.isTimeModified()) {
			if(this.data.id) {
				this.patch({start, duration},undefined, onCancel); // quick save
			} else {
				this.data.start = start;
				this.data.duration = duration;
				this.open(onCancel); // open dialog
			}
		}
	}

	open(onCancel?: Function) {
		//if (!ev.data.id) {
		const dlg = !this.isOwner ? new EventDetailWindow() : new EventWindow();
		if(dlg instanceof EventWindow) {
			dlg.on('close', () => {
				// cancel ?
				onCancel && onCancel();
				// did we save then show loading circle instead
				if (!this.key) // new
					Object.values(this.divs).forEach(d => d.remove());

			})
		}
		dlg.show();
		dlg.loadEvent(this);
	}

	downloadIcs(){
		client.getBlobURL('community/calendar/ics/'+this.key).then(window.open)
	}

	confirmScheduleMessage(modified: Partial<CalendarEvent>|false, onAccept: ()=>void) {
		Object.assign(this.data, modified);
		const type = this.shouldSchedule(modified);
		if(type) {
			const askScheduleWin = win({
					width: 550,
					modal: true,
					closable: false,
					title: t(type+'ScheduleTitle'),
				},
				comp({cls: 'pad flow'},
					comp({tagName:'i',cls:'icon',html:'email', width:100, style:{fontSize:'3em'}}),
					comp({html: t(type+'ScheduleText'), flex:1}),
				),
				tbar({},
					btn({
						text: t('Cancel'), handler: () => {
							askScheduleWin.close()
						}
					}), '->',
					btn({
						text: t('Send'), cls:'primary', handler: () => {
							eventDS.setParams.sendSchedulingMessages = true;
							onAccept();
							askScheduleWin.close()
						}
					})
				));
			askScheduleWin.show();
		} else {
			onAccept();
		}
	}

	get isOwner() {
		return !this.participants || this.calendarPrincipal?.roles?.owner || false;
	}

	get calendarPrincipal() {
		if(this.participants && this.principalId)
			return this.participants[this.principalId];
	}
	get principalId() {
		return (this.cal && this.cal.ownerId) ? this.cal.ownerId+'' : go.User.id+''
	}

	private get isInPast() {
		let lastOccurrence = this.end;
		if(this.isRecurring && this.data.recurrenceRule) {
			const e = this.data;
			if(e.recurrenceRule.count) {
				const r = new Recurrence({dtstart: new Date(e.start), timeZone:e.timeZone, rule: e.recurrenceRule});
				for(const date of r.loop(new DateTime(), new DateTime('2058-01-01'), (_d,i) => i < 1)){
					lastOccurrence = date.clone().add(new DateInterval(e.duration));
				}
			} else if(e.recurrenceRule.until) {
				lastOccurrence = (new DateTime(e.recurrenceRule.until))
				if(e.recurrenceRule.until.length === 10)
					lastOccurrence.addDays(1); // if date-only date is inclusive
				lastOccurrence.add(new DateInterval(e.duration));
			} else {
				return false; // never in this past when never ending recurrence
			}
		}
		return lastOccurrence.date < new Date();
	}

	updateParticipation(status: "accepted"|"tentative"|"declined") {
		if(!this.calendarPrincipal)
			throw new Error('Not a participant');
		this.calendarPrincipal.participationStatus = status;

		eventDS.setParams.sendSchedulingMessages = true;
		return eventDS.update(this.data.id, {participants: this.participants});
	}

	shouldSchedule(modified: Partial<CalendarEvent>|false) {
		if((!this.data.isOrigin && this.key) || this.isInPast)
			return;
		if(modified === false) {
			if(this.participants && !this.isCancelled) {
				return this.isOwner ? 'cancel' : 'delete';
			}
			return undefined;
		}
		 if(modified.participants || this.participants) {
			if(!this.key) {
				return 'new';
			} else {
				console.log(modified);
				if(['start','duration','end','description','title','showWithoutTime', 'location','participants', 'recurrenceRule']
					.some(k => modified.hasOwnProperty(k)))
				{
					return 'update';
				}
			}
		}
	}

	patch(modified: any, onFinish?: () => void, onCancel?: () => void) {
		if(!this.isRecurring) {
			this.confirmScheduleMessage(modified, () => {
				eventDS.update(this.data.id, modified); // await?
			});
		} else if(this.isOverride) {
			this.patchOccurrence(modified, onFinish);
		} else {
			// if(modified.recurrenceRule) {
			// 	eventDS.update(this.data.id, {recurrenceRule:modified.recurrenceRule});
			// 	delete modified.recurrenceRule;
			// }
			// if(Object.keys(modified).length === 0)
			// 	return
			const isFirstInSeries = this.data.start == this.recurrenceId
			const w = win({
					title: t('Do you want to edit a recurring event?'),
					width:550,
					modal: true,
					listeners: {'close': (_me,byUser) => { if(byUser && onCancel) onCancel();  }}
				},comp({cls: 'pad flow'},
					comp({tagName:'i',cls:'icon',html:'event_repeat', width:100, style:{fontSize:'3em'}}),
					comp({html: t('You will be editing a recurring event. Do you want to edit this occurrence only or all future occurrences?'), flex:1}),
				),tbar({},'->',
					btn({
						text: t('This event'),
						hidden: modified.recurrenceRule, // user must change future if rrule is modified
						cls:'primary',
						handler: _b => {
							this.patchOccurrence(modified, onFinish); w.close();
						}
					}),
					btn({
						text: t(isFirstInSeries ? 'All events' : 'This and future events'), // save to series
						handler: _b => {
							w.close();
							isFirstInSeries ?
								this.patchSeries(modified, onFinish) :
								this.patchThisAndFuture(modified, onFinish);

						}
					})
				)
			)
			w.show();
		}
	}

	private patchSeries(modified: any, onFinish?: () => void) {
		this.confirmScheduleMessage(modified, () => {
			const p = eventDS.update(this.data.id, modified);
			if(onFinish) p.then(onFinish);
		})
	}

	// todo: per-user -per-override properties ['alert','participants'[n].participationStatus]
	private static overridableProperties = ['start', 'duration', 'title', 'freeBusyStatus', 'participants','location','status', 'description']

	private patchOccurrence(modified: any, onFinish?: () => void) {
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
			this.confirmScheduleMessage(modified, () => {
				for(const name of CalendarItem.overridableProperties) {
					if(o[name] == original[name]) delete o[name]; // remove properties that are the same as original TODO alerts and participants cannot be compared like this
				}
				this.data.recurrenceOverrides![this.recurrenceId!] = o;
				const p = eventDS.update(this.data.id, {recurrenceOverrides:this.data.recurrenceOverrides});
				if(onFinish) p.then(onFinish);
			});
		});
	}

	/**
	 * @see  https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-10.html#section-5.5
	 */
	private patchThisAndFuture(modified: any, onFinish?: () => void) {
		//if(!ev.data.participants) { // is not scheduled ( split event)
		// todo: add first and next relation in relatedTo property as per https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-11.html#name-splitting-an-event
		const rule = structuredClone(this.data.recurrenceRule);
		// we might have changed start, so we'll take the actual recurrenceId
		rule.until = new DateTime(this.recurrenceId).addDays(-1).format('Y-m-d'); // close current series yesterday
		this.confirmScheduleMessage(modified, () => {
			eventDS.update(this.data.id, {recurrenceRule: rule}); // set until on original

			const next = Object.assign({},
				this.data,
				{start: this.recurrenceId!, id: null, uid: null},
				modified
			);
			const p = eventDS.create(next); // create duplicate
			if (onFinish) p.then(onFinish);
		});
	}

	remove() {
		if(!this.isRecurring) {
			this.confirmScheduleMessage(false, () => {
				eventDS.destroy(this.data.id);
				Object.values(this.divs).forEach(d => d.remove())
			});
		} else {
			const isFirstInSeries = this.data.start == this.recurrenceId;
			const w = win({
					title: t('Do you want to delete a recurring event?'),
					modal: true,
					width: 540,
				},comp({
					cls:'pad',
					html: t('You will be deleting a recurring event. Do you want to delete this occurrence only or all future occurrences?'),
				}),tbar({},btn({
						text: t('This event'),
						cls:'primary',
						handler: _b => { this.removeOccurrence(); w.close(); }
					}),btn({
						hidden: isFirstInSeries,
						text: t('This and future events'),
						handler: _b => { this.removeFutureEvents(); w.close(); }
					}),'->',btn({
						text: t('All events'), // the series
						handler: _b => { this.removeSeries(); w.close(); }
					})
				)
			)
			w.show();
		}
	}

	private removeFutureEvents() {
		this.confirmScheduleMessage(false, () => {
			this.data.recurrenceRule.until = (new DateTime(this.recurrenceId)).addDays(-1).format('Y-m-d'); // could be minus 1 seconds, but we don't recur within day
			eventDS.update(this.data.id,{recurrenceRule: this.data.recurrenceRule});
		});
	}

	private removeOccurrence() {
		this.confirmScheduleMessage(false, () => {
			this.data.recurrenceOverrides ??= {};
			this.data.recurrenceOverrides[this.recurrenceId!] = {excluded: true};
			eventDS.update(this.data.id, {recurrenceOverrides: this.data.recurrenceOverrides});
		});
	}

	private removeSeries() {
		this.confirmScheduleMessage(false, () => {
			eventDS.destroy(this.data.id);
		});
	}

	undoException(recurrenceId: string) {
		delete this.data.recurrenceOverrides![recurrenceId];
		return eventDS.update(this.data.id, {recurrenceOverrides:this.data.recurrenceOverrides});
	}
}