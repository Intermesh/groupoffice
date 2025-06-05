import {CalendarView} from "./CalendarView.js";
import {DateTime, E} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {client} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

class CalendarDayItem extends CalendarItem {
	pos!: number
	lanes!: number
	startM!:number
	endM!: number // not needed after calculation
}

export class WeekView extends CalendarView {

	dayCols: {[dayStartYMD:string]: HTMLElement} = {}
	dayItems : CalendarDayItem[] = []
	alldayCtr!: HTMLElement
	baseCls = 'cal week';

	protected internalRender() {
		this.makeDraggable();
		this.el.tabIndex = 0;

		this.el.on('keydown', (e: KeyboardEvent) => {
			if(e.key == 'Delete') {
				this.selected.forEach(item => {
					const i = this.dayItems.indexOf(item as CalendarDayItem);
					if(i > -1) {
						item.remove();
					}
				});
			}
		});

		return super.internalRender();
	}

	goto(day: DateTime, amount: number) {
		if(!day) {
			day = new DateTime();
		}
		// if(day.format('Ymd') === this.day.format('Ymd') && this.days === amount) {
		// 	this.update();
		// 	return;
		// }

		this.days = amount;
		this.day = day.setHours(0,0,0,0);
		// const startWeek = this.day.setWeekDay(0),
		// 	endWeek = startWeek.clone().addDays(7);
		this.renderView();
		this.adapter.goto(day, day.clone().addDays(amount));

		// Object.assign(this.store.queryParams.filter ||= {}, {
		// 	after: day.format('Y-m-d'),
		// 	before: day.clone().addDays(amount).format('Y-m-d')
		// });
		//
		// this.store.load();
		//this.store.filter('date', {after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00')}).fetch(0,500);


	}

	private makeDraggable() {

		let ev: CalendarDayItem,
			SNAP = client.user.calendarPreferences.weekViewGridSnap,
			changed: boolean,
			offset: number,
			last:number,
			anchor: number,
			from : number,
			till: number,
			currDayEl: HTMLElement,
			pxPerSnap: number,
			action: (m:number) => [number, number];

		const move: typeof action = m => [m, m+(ev.end.getMinuteOfDay()-ev.start.getMinuteOfDay())],
			resize: typeof action = m => (m > anchor) ? [anchor,m] : [m,anchor];

		const mouseMove = (e: MouseEvent & {target: HTMLElement}) => {
			e.preventDefault();
			const minute = Math.round((e.clientY - offset) / pxPerSnap) * SNAP,
				dragDayEl = e.target.up('dd[data-day]');
			if(!dragDayEl) return;
			if(dragDayEl !== currDayEl) { // move to different day
				const a = Array.from(dragDayEl.parentElement!.children),
					diff = a.indexOf(dragDayEl) - a.indexOf(currDayEl),
					prevDay = ev.start.clone();

				ev.start.addDays(diff);
				ev.end.addDays(diff);
				currDayEl = dragDayEl;
				this.dayItems.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
				Object.values(ev.divs).forEach(d => d.remove());
				ev.divs = {};
				changed = true;
				this.updateItems(ev.start.clone());
				this.updateItems(prevDay);
			}
			if(minute !== last) { // move to along same day
				last = minute;
				[from, till] = action(minute);
				if(from === till) return;

				ev.start.setHours(0, from);
				ev.end.setHours(0, till);
				const firstDiv = Object.values(ev.divs)[0];
				if(firstDiv)
					firstDiv.lastElementChild!.textContent = ev.start.format('G:i') + ' - ' + ev.end.format('G:i');
				changed = true;
				this.updateItems(ev.start.clone());
			}

		},
		mouseUp = (_e:MouseEvent) => {
			this.el.cls('-resizing');
			this.el.un('mousemove', mouseMove);
			changed && ev.save(() => {
				//this.dayItems.shift()
				this.populateViewModel();
				//this.updateItems();
			});
		};

		this.el.on('mousedown', (e: MouseEvent) => {
			SNAP = client.user.calendarPreferences.weekViewGridSnap;
			changed = false;
			if (e.button !== 0) return;

			const li = this.el.lastElementChild!.lastElementChild as HTMLElement,
				target = e.target as HTMLElement;
			pxPerSnap = li.offsetHeight / (1440 / SNAP); // 96 quarter-hours in a day
			offset = li.getBoundingClientRect().top;
			currDayEl = target.up('[data-day]')!;
			const event = target.up('div[data-key]');
			if (event) { // MOVE
				offset += e.offsetY;
				ev = this.dayItems.find(m => m.key == event.dataset.key)!;

				if(!ev || !ev.isOwner) return;
				action = resize;
				this.el.cls('+resizing');
				// 4 pixels for the resize handle on top and bottom of event
				if (e.offsetY <= 4) {
					anchor = ev.end.getMinuteOfDay();
				} else if (e.offsetY >= event.offsetHeight - 4) {
					anchor = ev.start.getMinuteOfDay();
					offset -= event.offsetHeight;
				} else {
					action = move;
					this.el.cls('-resizing');
				}
				this.el.on('mousemove', mouseMove);
			}
			if(target.isA('dd')) { // CREATE
				anchor = Math.round(e.offsetY / pxPerSnap) * SNAP;
				const data = {
						start: (new DateTime(target.dataset.day!)).setHours(0, anchor).format('c'),
						title: t('New event'),
						duration: client.user.calendarPreferences.defaultDuration ?? "PT1H",
						calendarId: CalendarView.selectedCalendarId,
						showWithoutTime: false
					},
					start = new DateTime(data.start),
					end = start.clone().addHours(1);
				ev = new CalendarDayItem({start,end,data,key:''});
				this.dayItems.unshift(ev);
				this.updateItems(start.clone().setHours(0,0,0,0));
				action = resize;
				changed = true;
				this.el.on('mousemove', mouseMove);
			}
			window.addEventListener('mouseup', mouseUp,{once:true});
		});
	}

	protected clear() {
		this.dayItems.forEach(ev => {
			Object.values(ev.divs).forEach(d => d.remove());
		})
		this.dayItems = [];
		super.clear();
	}

	protected populateViewModel() {
		this.clear();
		//const viewEnd = this.day.clone().addDays(this.days);
		const allDay = [],
			withTime = []
		// for (const e of this.store.items) {
		//
		// 	const items = CalendarItem.expand(e as CalendarEvent, this.day, viewEnd);
		// 	if(e.showWithoutTime) {
		// 		allDay.push(...items as CalendarItem[]);
		// 	} else {
		// 		withTime.push(...items as CalendarDayItem[]);
		// 	}
		// }
		for(const item of this.adapter.items) {
			if(item.data?.showWithoutTime) {
				allDay.push(item);
			} else {
				withTime.push(item as CalendarDayItem);
			}
		}

		this.viewModel = allDay.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
		this.dayItems = withTime.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
		this.updateFullDayItems();
		this.updateItems();
	}

	renderView() {
		const oldScrollTop = this.el.lastElementChild?.scrollTop;
		this.el.innerHTML = ''; // clear
		let now = new DateTime(),
			hour, day = this.day.clone();
		//day.setWeekDay(0);

		let heads = [], days = [],fullDays = [], hours = [], showNowBar=false ,nowbar;
		for (hour = 1; hour < 24; hour++) {
			hours.push(E('em', hour+':00'));
		}

		this.dayCols = {};
		for (var i = 0; i < this.days; i++) {

			heads.push(E('li',day.format('D'),E('em',day.getDate()))
				.cls('past', day.format('Ymd') < now.format('Ymd'))
				.cls('today', day.format('Ymd') === now.format('Ymd'))
			);
			const dayContainer = E('dd').cls('weekend',day.getDay()%6==0).attr('data-day', day.format('Y-m-d'));
			this.dayCols[day.format('Ymd')] = dayContainer;
			fullDays.push(E('li').cls('weekend',day.getDay()%6==0).attr('data-day', day.format('Y-m-d')))
			days.push(dayContainer);
			if(now.format('Ymd') === day.format('Ymd')) {
				showNowBar = true;
			}
			day.addDays(1);
		}
		if(showNowBar) {
			// an hour is 8vh
			const top = 8 / 60 * now.getMinuteOfDay(), // 1296 = TOTAL HEIGHT of DAY
				left = 100 / this.days * (now.getWeekDay() - this.day.getWeekDay());
			nowbar = E('div', E('hr'),
				E('b').attr('style', `left: ${left}%;`),
				E('span', now.format('G:i'))
			).cls('now').attr('style', `top:${top}vh;`)
		}
		let ol: HTMLElement;

		this.el.append(
			E('ul',E('li',t('Wk')+' '+this.day.getWeekOfYear()).cls('current',showNowBar), ...heads),
			E('ul',E('li', t('All day')), this.alldayCtr = E('li').cls('all-days'), ...fullDays),
			ol = E('dl',E('dt', nowbar || '', E('em'), ...hours), ...days)
		);
		setTimeout(() => ol.scrollTop = oldScrollTop || (ol.scrollHeight / 4)); // = scroll 6hours down (1/4 of day)
	}

	private updateFullDayItems() {

		this.slots = Array.from({length: this.days}, _ => ({}) );

		this.alldayCtr.innerHTML = '';
		this.alldayCtr.prepend(...this.viewModel.map(e =>
			super.eventHtml(e).css(this.makestyle(e, this.day))
		));

		var lengths = this.slots.map((i: any) => Object.keys(i).length);
		this.alldayCtr.parentElement!.style.height = (Math.max(...lengths,1) * this.ROWHEIGHT)+'rem';
	}

	private updateItems(day?: DateTime) {

		this.continues = [];
		this.iter = 0;
		if(day) {
			// update specified day
			this.drawDay(day.setHours(0,0,0,0), this.dayCols[day.format('Ymd')])
		} else {
			// update all visible days
			for (const ymd in this.dayCols) {
				this.drawDay(DateTime.createFromFormat(ymd, 'Ymd')!, this.dayCols[ymd])
			}
		}
		// call draw week but re-use divs only set style ignore the return value
	}

	iter!: number
	continues: CalendarDayItem[] = []

	private drawDay(dayStart: DateTime, dd: HTMLElement) {
		let stillContinueing = [],
			e: any,
			eventEls = [],
			end = dayStart.clone().addDays(1);
		while((e = this.dayItems[this.iter]) && e.start.format('Ymd') < end.format('Ymd')) {
			if(e.end.date > dayStart.date) {
				this.continues.push(e);
			}
			this.iter++;
		}
		if(this.continues.length)
			this.calculateOverlap(this.continues, dayStart);
		while(e = this.continues.shift()) {
			eventEls.push(this.drawEvent(e, dayStart, dd));
			if(e.end.date > end.date) {
				stillContinueing.push(e); // push it back for next week
			}
		}
		this.continues = stillContinueing;
		return eventEls;
	}

	/**
	 * Never ever touch this function. You have been warned.
	 */
	protected calculateOverlap(events: CalendarDayItem[], dayStart: DateTime) {
		//events.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
		let highestEnd = 0,
			blockStart = 0,
			blockLanes = 1;
		for(let i = 0; i < events.length; i++) {
			const a = events[i]; // current item
			a.startM =  a.start.format('Ymd') < dayStart.format('Ymd') ? 0 : a.start.getMinuteOfDay();
			a.endM = a.end.format('Ymd') > dayStart.format('Ymd') ? 1440 : a.end.getMinuteOfDay();
			a.pos = 0;
			if(a.startM >= highestEnd) { // end collision block
				blockStart = i
				blockLanes = 1;
			}
			for(let j = blockStart; j < i; j++) {
				const b = events[j]; // already positioned item
				if(a.endM > b.startM && a.startM < b.endM && a.pos === b.pos) { // collides
					a.pos++;
					blockLanes = Math.max(blockLanes, a.pos+1);
					j = blockStart-1; // restart from blockstart
				}
				b.lanes = blockLanes;
			}
			a.lanes = blockLanes;
			highestEnd = Math.max(highestEnd,a.endM);
		}
	}

	protected drawEvent(e: CalendarDayItem, dayStart: DateTime, dd: HTMLElement) {
		const i = dayStart.format('Ymd')
		if(!e.divs[i]) {
			e.divs[i] = super.eventHtml(e);
		}
		if(!e.divs[i].isConnected)
			dd.append(e.divs[i]);
		return e.divs[i].css({
			color: '#'+e.color,
			top: (100 / 1440 * e.startM)+'%',
			left: (e.pos * (100 / e.lanes))+'%',
			width: (100 / e.lanes) +'%',
			height: (100 / 1440 * (e.endM - e.startM))+'%'
		});
	}

}