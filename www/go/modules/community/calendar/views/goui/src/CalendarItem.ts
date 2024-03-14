import {
	BaseEntity,
	btn,
	comp,
	DateInterval,
	DateTime,
	E, EntityID, MaterialIcon,
	t,
	tbar, Timezone,
	win
} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {client, jmapds, Recurrence} from "@intermesh/groupoffice-core";
import {EventWindow} from "./EventWindow.js";
import {EventDetailWindow} from "./EventDetail.js";

export interface CalendarEvent extends BaseEntity {
	id: EntityID
	recurrenceRule?: any
	recurrenceOverrides?: any
	links?: any
	alerts?: any
	showWithoutTime?: boolean // isAllDay
	duration: string
	start: string
	timeZone:Timezone
	title: string
	color?: string
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
	title?: string
	start?: DateTime
	end?: DateTime
	open?:() => void
	//color?: string
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
	private extraIcons;
	//color!: string

	private initStart: string
	private initEnd: string

	private cal: any

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

			const r = new Recurrence({dtstart: new Date(e.start), rule: e.recurrenceRule});
			//let rEnd = r.current.clone().add(new DateInterval(e.duration));
			for(const date of r.loop(from, until)){
			//while(r.current.date < until.date && rEnd.date > from.date) {
				let rEnd = date.clone().add(new DateInterval(e.duration));

				const recurrenceId = date.format('Y-m-d\Th:i:s');
				if (e.recurrenceOverrides && recurrenceId in e.recurrenceOverrides) {
					const o = e.recurrenceOverrides[recurrenceId];
					if(o.excluded) {
						// excluded
					} else {
						const overideStart = o.start ? new DateTime(o.start) : date.clone();
						yield new CalendarItem({
							key: e.id + '/' + recurrenceId,
							recurrenceId: recurrenceId,
							start: overideStart,
							title: o.title || e.title,
							end: (o.duration || o.start) ? overideStart.clone().add(new DateInterval(o.duration || e.duration)) : rEnd,
							data: e
							//color: o.color??null
						});
					}
				} else {
					yield new CalendarItem({
						key: e.id + '/' + recurrenceId,
						recurrenceId: recurrenceId,
						start: date.clone(),
						end: rEnd,
						data: e
					});
				}
				// if(!r.next()) {
				// 	break;
				// }
				// rEnd = r.current.clone().add(new DateInterval(e.duration));
				//}
				//} while(r.current.date < until.date && r.next())
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

	remove() {
		if(!this.isRecurring) {
			this.confirmScheduleMessage(false, () => {
				eventDS.destroy(this.data.id);
				Object.values(this.divs).forEach(d => d.remove())
			});
		} else {
			const w = win({
					title: t('Do you want to delete a recurring event?'),
					modal: true,
					width: 500,
				},comp({
					cls:'pad',
					html: t('You will be deleting a recurring event. Do you want to delete this occurrence only or all future occurrences?'),
				}),tbar({},btn({
						text: t('This event'),
						cls:'primary',
						handler: _b => { this.removeOccurrence(); w.close(); }
					}),btn({
						text: t('This and future events'),
						handler: _b => { this.removeFutureEvents(); w.close(); }
					}),'->',btn({
						text: t('All events'), // the series
						handler: _b => { eventDS.destroy(this.data.id); w.close(); }
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
		return (this.recurrenceId && this.data.recurrenceOverrides && this.recurrenceId in this.data.recurrenceOverrides);
	}

	get isDeclined() {
		return this.currentParticipant?.participationStatus === 'declined';
	}

	get color() {
		return this.data.color || this.cal?.color || '356772';
	}

	/** amount of days this event is spanning */
	get dayLength() {
		// 1 day + the distance in days between start and end. - 1 second of end = 00:00:00
		return 1 + this.start.diff(this.end.clone().addSeconds(-1)).getTotalDays()!;
	}

	get icons() {
		const e = this.data;
		const icons = this.extraIcons;
		if(e.recurrenceRule) icons.push('refresh');
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');
		if(!!e.participants) icons.push('group');
		if(!icons.length && !e.showWithoutTime) {
			icons.push('fiber_manual_record') // the dot
		}
		return icons.map(i=>E('i',i).cls('icon'));
	}

	save(onCancel: () => void) {
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
		return !this.data.participants || this.currentParticipant?.roles?.owner || false;
	}

	get currentParticipant() {
		if(!this.data.participants)
			return
		return this.data.participants[this.participantId];
	}

	private get isInPast() {
		return this.end.date < new Date();
	}

	private get participantId() {
		return (this.cal && this.cal.ownerId) ? this.cal.ownerId+'' : go.User.id+''
	}

	updateParticipation(status: "accepted"|"tentative"|"declined") {
		if(!this.data.participants || !this.data.participants[this.participantId])
			throw new Error('Not a participant');
		this.data.participants[this.participantId].participationStatus = status;

		eventDS.setParams.sendSchedulingMessages = true;
		eventDS.update(this.data.id, {participants: this.data.participants});
	}

	shouldSchedule(modified: Partial<CalendarEvent>|false) {
		if((!this.data.isOrigin && this.key) || this.isInPast)
			return;
		if(modified === false) {
			return this.data.participants ? 'cancel' : undefined;
		}
		 if(modified.participants || this.data.participants) {
			if(!this.key) {
				return 'new';
			} else {
				if(['start','duration','end','description','title','showWithoutTime','isAllDay', 'location','participants']
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
			const isFirstInSerie = this.data.start === this.recurrenceId,
				w = win({
					title: t('Do you want to edit a recurring event?'),
					width:550,
					modal: true,
					listeners: {userclosed: () => { if(onCancel) onCancel();  }}
				},comp({cls: 'pad flow'},
					comp({tagName:'i',cls:'icon',html:'event_repeat', width:100, style:{fontSize:'3em'}}),
					comp({html: t('You will be editing a recurring event. Do you want to edit this occurrence only or all future occurrences?'), flex:1}),
				),tbar({},'->',btn({
						text: t('This event'),
						cls:'primary',
						handler: _b => { this.patchOccurrence(modified, onFinish); w.close(); }
					}),btn({
						text: t(isFirstInSerie ? 'All events' : 'This and future events'), // save to series
						handler: _b => {
							const p = isFirstInSerie ?
								eventDS.update(this.data.id, modified) :
								this.patchThisAndFuture(modified);
							if(onFinish) p.then(onFinish);
							w.close();
						}
					})
				)
			)
			w.show();
		}
	}

	private static overridableProperties = ['start', 'duration', 'title', 'freeBusyStatus', 'participants','location','alerts', 'description']

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
				this.data.recurrenceOverrides[this.recurrenceId!] = o;
				const p = eventDS.update(this.data.id, {recurrenceOverrides:this.data.recurrenceOverrides});
				if(onFinish) p.then(onFinish);
			});
		});
	}

	/**
	 * @see  https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-10.html#section-5.5
	 */
	private patchThisAndFuture(modified: any) {
		//if(!ev.data.participants) { // is not scheduled ( split event)
		// todo: add first and next relation in relatedTo property as per https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-11.html#name-splitting-an-event
		const rule = structuredClone(this.data.recurrenceRule);
		rule.until = this.start.addDays(-1).format('Y-m-d'); // close current series yesterday
		eventDS.update(this.data.id, {recurrenceRule: rule}); // set until on original

		const next = Object.assign({},
			this.data,
			{start: this.recurrenceId!, id:null, uid:null},
			modified
		);
		return eventDS.create(next); // create duplicate
	}

	private removeFutureEvents() {
		this.data.recurrenceRule.until = (new DateTime(this.recurrenceId)).addDays(-1).format('Y-m-d'); // could be minus 1 seconds but we don't recur within day
		eventDS.update(this.data.id,{recurrenceRule: this.data.recurrenceRule});
	}

	undoException(recurrenceId: string) {
		delete this.data.recurrenceOverrides[recurrenceId];
		return eventDS.update(this.data.id, {recurrenceOverrides:this.data.recurrenceOverrides});
	}

	private removeOccurrence() {
		this.confirmScheduleMessage(false, () => {
			this.data.recurrenceOverrides ??= {};
			this.data.recurrenceOverrides[this.recurrenceId!] = {excluded: true};
			eventDS.update(this.data.id, {recurrenceOverrides: this.data.recurrenceOverrides});
		});
	}
}