import {
	BaseEntity,
	btn,
	comp,
	DateInterval,
	DateTime,
	E, EntityID, MaterialIcon, ObjectUtil, root,
	tbar, Timezone,
	win, Window
} from "@intermesh/goui";
import {calendarStore, categoryStore, t} from "./Index.js";
import {client, jmapds, Recurrence} from "@intermesh/groupoffice-core";
import {EventWindow} from "./EventWindow.js";
import {EventDetailWindow} from "./EventDetail.js";
import {SubscribeWindow} from "./SubscribeWindow";

export type RecurrenceOverride = (Partial<CalendarEvent> & {excluded?:boolean});
export type RecurrenceOverrides = {[recurrenceId:string]: RecurrenceOverride};
export interface CalendarEvent extends BaseEntity {
	id: EntityID
	recurrenceRule?: any
	recurrenceOverrides?: RecurrenceOverrides
	links?: any
	alerts?: any
	showWithoutTime?: boolean // isAllDay
	duration: string
	start: string
	timeZone:Timezone
	title: string
	color?: string
	categoryIds: string[]
	status?: string
	isOrigin: boolean
	participants?: {[key:string]: any}
	calendarId: string
}

export interface CalendarCategory extends BaseEntity {
	name: string
	color: string
}

const eventDS = jmapds('CalendarEvent');

interface CalendarItemConfig {
	key: string|null// id/recurrenceId or null if not in the database put parsed from invitation ics
	recurrenceId?:string
	extraIcons?: MaterialIcon[]
	data: Partial<CalendarEvent>
	override?: Partial<CalendarEvent>
	title?: string
	start?: DateTime
	end?: DateTime
	open?:() => void
	defaultColor?: string
}

/**
 * This is the ViewModel for items displaying in the calendar.
 * For now, they can be generated from the CalendarEvent model.
 * Because if recurrence (and overridden) 1 CalendarEvent may return multiple items
 */
export class CalendarItem {

	key!: string|null // id/recurrenceId
	recurrenceId?:string
	data!: CalendarEvent
	override?: any // is patch object with props like "participants/u1/participationStatus" Partial<CalendarEvent>
	title!: string
	start!: DateTime
	end!: DateTime
	patched: CalendarEvent
	readonly extraIcons;
	//color!: string

	readonly initStart: string
	readonly initEnd: string

	cal: any
	categories : CalendarCategory[] = []

	divs: {[week: string] :HTMLElement}
	defaultColor?: string

	constructor(obj:CalendarItemConfig) {
		Object.assign(this,obj);


		this.patched = ObjectUtil.patch(structuredClone(obj.data), obj.override) as CalendarEvent;
		 if(obj.recurrenceId && (!obj.override || !obj.override.start))
		 	this.patched.start = obj.recurrenceId;
 		// if(obj.override)
		 // 	debugger;
		if(!obj.start) {
			this.start = new DateTime(this.patched.start);
			if(this.data.showWithoutTime) {
				this.start.setHours(0,0,0,0);
			}
		}
		if(obj.data.timeZone) {
			this.start.timezone = this.patched.timeZone;
			this.start = this.start.toTimezone(client.user.timezone as Timezone);
		}
		//if(!obj.end) {
			this.end = this.start.clone().add(new DateInterval(this.patched.duration!));
		//}
		this.cal = calendarStore.find((c:any) => c.id == this.patched.calendarId);
		if(this.patched.categoryIds)
		for(const id of this.patched.categoryIds) {
			const cat = categoryStore.find((c:any) => c.id == id);
			if(cat)
				this.categories.push(cat as CalendarCategory);
		}

		this.initStart = this.start.format('Y-m-d\TH:i:s');
		this.initEnd = this.end.format('Y-m-d\TH:i:s');

		if(!obj.title) {
			this.title = this.patched.title!;
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

	patchedInstance(recurrenceId:string) {
		// debugger;
		if(!this.data.recurrenceRule || !this.data.recurrenceOverrides || !this.data.recurrenceOverrides[recurrenceId]) {
			throw "Not found";
		}

		const o = this.data.recurrenceOverrides![recurrenceId] as RecurrenceOverride;

		if(o.excluded) {
			throw "Not found";
		}

		return new CalendarItem({key: this.data.id + '/' + recurrenceId, recurrenceId, override: o, data: this.data});
	}

	static *expand(e: CalendarEvent, from: DateTime, until: DateTime) : Generator<CalendarItem> {
		const start = new DateTime(e.start),
			end = start.clone().add(new DateInterval(e.duration));

		if(e.recurrenceRule) {
			if(e.recurrenceOverrides) {
				for(const recurrenceId in e.recurrenceOverrides) {
					const o = e.recurrenceOverrides[recurrenceId];
					if(o.excluded) continue;
					const oStart = new DateTime(o.start ?? recurrenceId);
					if(oStart.date > from.date) {
						const oEnd = oStart.add(new DateInterval(o.duration ?? e.duration));
						if(oEnd.date < until.date) {
							yield new CalendarItem({key: e.id + '/' + recurrenceId, recurrenceId, override: o, data: e});
						}
					}
				}
			}
			const r = new Recurrence({dtstart: new Date(e.start), timeZone:e.timeZone, rule: e.recurrenceRule});
			for(const date of r.loop(from, until)){
				const recurrenceId = date.format('Y-m-d\TH:i:s');

				if (e.recurrenceOverrides && recurrenceId in e.recurrenceOverrides) {
					// const o = e.recurrenceOverrides[recurrenceId];
					// if(!o.excluded) {
					// 	yield new CalendarItem({key: e.id + '/' + recurrenceId, recurrenceId, override: o, data: e});
					// }
				} else {
					yield new CalendarItem({key: e.id + '/' + recurrenceId, recurrenceId, data: e});
				}
			}
		} else if (end.date > from.date && start.date < until.date) {
			yield new CalendarItem({key: e.id+"", data:e});
		}
	}

	get isRecurring() {
		return (this.key && this.key.includes('/')) || this.data.recurrenceRule;
	}

	get isOverride() {
		return (this.recurrenceId && this.data.recurrenceOverrides && this.recurrenceId in this.data.recurrenceOverrides);
	}

	get isDeclined() {
		return this.calendarPrincipal?.participationStatus === 'declined';
	}

	get isCancelled() {
		return this.patched.status === 'cancelled';
	}

	get isTentative() {
		return  this.calendarPrincipal?.participationStatus === 'tentative';
	}

	get needsAction() {
		return this.calendarPrincipal?.participationStatus === 'needs-action' && !this.isOwner;
	}

	get color() {
		return this.cal?.color || this.defaultColor || '356772';
	}

	get participants() {
		return this.patched.participants;
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
		//console.log(this.title, this.start.diff(this.end.clone().addSeconds(-1)));
		return 1 + this.start.diff(this.end.clone().addSeconds(-1)).getTotalDays()!;
	}

	get categoryDots() {
		for (const cat of this.categories) {
			return [E('i').cls('cat').attr('title',cat.name).css({color: '#'+cat.color})];
		}
		return [];
	}
	get icons() {
		const e = this.data;
		const icons = [...this.extraIcons];
		//if(e.recurrenceRule) icons.push('refresh');
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

		const internalOpen = () => {
			const dlg = !this.isOwner ? new EventDetailWindow() : new EventWindow();
			if (dlg instanceof EventWindow) {
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

			return dlg;
		}
		const cals = calendarStore.all()
		if(!cals.length) {
			const w = win({
				title: t('No writeable calendars'),
				width: 520
			},
				comp({cls:'pad',html:t('There are no calendars to add an appointment.')+'<br>'+t('Subscribe to an existing calendar or create a personal calendar.')}),
				tbar({},
					btn({text: t('Show calendars')+'...',handler:()=>{
						const d = new SubscribeWindow();
						d.show();
						w.close();
						if(onCancel) onCancel();
					}}),
					btn({text: t('Create personal calendar'), handler:() => {
						client.jmap("Calendar/first", {}, 'pFirst').then(r => {
							calendarStore.reload().then(r2 => {
								this.data.calendarId = r.calendarId;
								internalOpen();
							});

						}).catch(r => {
							Window.error(r.message);
							if(onCancel) onCancel();
						});
						w.close();
					}})
				)
			);
			w.show();

		} else {
			internalOpen();
		}
	}

	private randomColor(seed: string) {
		const colors = [
			"CDAD00", "E74C3C", "9B59B6", "8E44AD", "2980B9", "3498DB",
			"1ABC9C", "16A085", "27AE60", "2ECC71", "F1C40F", "F39C12",
			"E67E22", "D35400", "95A5A6", "34495E", "808B96", "1652A1"
		];

		let hash = [...seed].reduce((acc, char) => acc + char.charCodeAt(0), 0);
		return colors[hash % colors.length];
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
					}),'->',
					btn({
						text: t('Save only'), handler: () => {
							Object.assign(this.data, modified);
							onAccept();
							askScheduleWin.close()
						}
					}),
					btn({
						text: t('Send'), cls:'primary', handler: () => {
							eventDS.setParams.sendSchedulingMessages = true;
							Object.assign(this.data, modified);
							onAccept();
							askScheduleWin.close()
						}
					})
				));
			askScheduleWin.show();
		} else {
			Object.assign(this.data, modified);
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

	updateParticipation(status: "accepted"|"tentative"|"declined", onFinish?: () => void) {

		if(!this.calendarPrincipal)
			throw new Error('Not a participant');
		this.calendarPrincipal.participationStatus = status;

		//eventDS.setParams.sendSchedulingMessages = true;
		// should we notify a reply is sent?
		this.patch({participants: this.participants}, onFinish,undefined,false);
	}

	shouldSchedule(modified: Partial<CalendarEvent>|false) {
		if(this.isInPast) // todo: use this.calendarPrincipal.expectReply if not owner ??
			return;

		if(modified === false) {
			if(this.participants && !this.isCancelled) {
				return this.isOwner ? 'cancel' : 'delete';
			}
			return undefined;
		}
		if(!this.isOwner) {
			return 'status';
		}
		if(modified.participants || this.participants) {
			if(!this.key) {
				return 'new';
			} else {
				if(['start','duration','end','description','title','showWithoutTime', 'location','participants', 'recurrenceRule']
					.some(k => modified.hasOwnProperty(k)))
				{
					return 'update';
				}
			}
		}
	}

	patch(modified: any, onFinish?: () => void, onCancel?: () => void, skipAsk = false) {
		if(!this.isRecurring) {
			this.confirmScheduleMessage(modified, () => {

				root.mask();

				const p = eventDS.update(this.data.id, modified).catch(e => {
					void Window.error(e);
					throw e;
				}).finally(() => {
					root.unmask();
				})

				if(onFinish)
					p.then(onFinish)

			});
		} else if(this.isOverride) {
			this.patchOccurrence(modified, onFinish);
		} else if(skipAsk) {
			// always patch series
			this.patchSeries(modified, onFinish)
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
						text: t('This and future events'),
						hidden : isFirstInSeries || !this.isOwner,
						handler: _b => {
							w.close();
							this.patchThisAndFuture(modified, onFinish);
						}
					}),
					btn({
						text: t('All events'), // save to series
						hidden: !isFirstInSeries && this.isOwner,
						handler: _b => {
							w.close();
							this.patchSeries(modified, onFinish);
						}
					})
				)
			)
			w.show();
		}
	}

	private patchSeries(modified: any, onFinish?: () => void) {
		this.confirmScheduleMessage(modified, () => {

			root.mask();
			const p = eventDS.update(this.data.id, modified)
				.catch(e => {
					void Window.error(e);
					throw e;
				}).finally(() => {
					root.unmask();
				})

			if(onFinish) p.then(onFinish);
		})
	}

	// todo: per-user -per-override properties ['alert']

	private patchOccurrence(modified: any, onFinish?: () => void) {
		//this.data.recurrenceOverrides ??= {};

		let patch: any = this.isOverride ? this.data.recurrenceOverrides && this.data.recurrenceOverrides[this.recurrenceId!] : {}

		eventDS.single(this.data.id).then(original => {
			if(!original) return; // why could this be undefined?
			this.confirmScheduleMessage(modified, () => {

				for(const name of ['start', 'duration', 'title', 'freeBusyStatus', 'location','status', 'description']) {
					if((name in modified) && modified[name] != original[name])
						patch[name] = modified[name]; // remove properties that are the same as original
				}
				if(modified.participants) {
					//remove all earlier participant patches as we will rebuild the patch completely.
					for(let key in patch) {
						if(key.startsWith("participants/")) {
							delete patch[key];
						}
					}

					for(const key in modified.participants) {
						const p = modified.participants[key];
						if(original.participants && key in original.participants) {
							// patch props that are different (escaped)
							for(const prop in p) {

								if(prop == "roles") {
									//roles is an object and therefore always different with !=. Maybe
									// just skip here as it never changes in an override.
									continue;
								}
								if(p[prop] != original.participants[key][prop]) {
									patch['participants/'+key+'/'+prop] = p[prop];
								}
							}
						} else {
							// patch the whole participant (when added)
							patch['participants/'+key] = p;
						}
					}

					// process removed participants
					if(original.participants) {
						for(const key in original.participants) {
							if(!(key in modified.participants)) {
								patch['participants/'+key] = null;
							}
						}
					}
				}
				// TODO: alerts

				root.mask();

				const data:any = {};
				if(this.data.recurrenceOverrides) {
					data['recurrenceOverrides/'+this.recurrenceId!] = patch;
				} else {
					data['recurrenceOverrides'] = {[this.recurrenceId!] : patch };
				}


				const prom = eventDS.update(this.data.id, data)
					.catch(e => {
					void Window.error(e);
					throw e;
				}).finally(() => {
						root.unmask();
					})
				if(onFinish) prom.then(onFinish)
			});
		});
	}


	/**
	 * When "This and future" is used then we should remove all patches from the event that occur after the "This and future" date.
	 * @param until
	 * @private
	 * @return The new "recurrenceOverrides" property
	 */
	private removeFutureOverrides(until: DateTime) {
		const patchRecurrenceOverride: RecurrenceOverrides = {};
		if(this.data.recurrenceOverrides) {
			// Copy recurrence overrides that occur before the until date
			for(const recurrenceId in this.data.recurrenceOverrides) {
				if((new DateTime(recurrenceId)).compare(until) == -1) {
					patchRecurrenceOverride[recurrenceId] = this.data.recurrenceOverrides[recurrenceId];
				}
			}
		}

		return patchRecurrenceOverride;
	}

	/**
	 * @see  https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-10.html#section-5.5
	 */
	private patchThisAndFuture(modified: any, onFinish?: () => void) {
		//if(!ev.data.participants) { // is not scheduled ( split event)
		// todo: add first and next relation in relatedTo property as per https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-11.html#name-splitting-an-event
		const rule = structuredClone(this.data.recurrenceRule);
		// we might have changed start, so we'll take the actual recurrenceId
		const until = new DateTime(this.recurrenceId).addDays(-1);
		rule.until = until.format('Y-m-d'); // close current series yesterday
		this.confirmScheduleMessage(modified, () => {

			const update : Partial<CalendarEvent> = {recurrenceRule: rule};

			// remove patches that occur after the until
			const patchRecurrenceOverrides = this.removeFutureOverrides(until);
			if(Object.keys(patchRecurrenceOverrides).length != Object.keys(this.data.recurrenceOverrides ?? {}).length) {
				update.recurrenceOverrides = patchRecurrenceOverrides;
			}

			eventDS.update(this.data.id, update); // set until on original

			const next = Object.assign({},
				this.data,
				{start: this.recurrenceId!},
				modified
			);

			delete next.id;
			delete next.uid;
			delete next.recurrenceOverrides;

			const p = eventDS.create(next)
				.catch(e => {
					void Window.error(e);
					throw e;
				}); // create duplicate
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
						hidden: isFirstInSeries || !this.isOwner /* remove this and future not supported for recurring invites */,
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
			if (this.isOwner) {

				this.data.recurrenceOverrides ??= {};
				this.data.recurrenceOverrides[this.recurrenceId!] = {excluded: true};
				eventDS.update(this.data.id, {recurrenceOverrides: this.data.recurrenceOverrides});
			} else {
				// set status to not participating
			}
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