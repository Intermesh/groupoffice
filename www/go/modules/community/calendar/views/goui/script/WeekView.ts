import {CalendarView} from "./CalendarView.js";
import {DateInterval, DateTime, E, Format} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {client} from "@intermesh/groupoffice-core";
import {allCalendarStore, t} from "./Index.js";

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
	private nowbar: HTMLElement | undefined;

	protected internalRender() {

		setInterval(() => {
			// update the now line every minute
			this.updateNowBar();
		}, 60000);

		this.makeDraggable();
		this.el.tabIndex = 0;

		this.el.on('keydown', (e: KeyboardEvent) => {
			if(e.key == 'Delete') {
				this.selected.forEach(item => {
					const i = this.dayItems.indexOf(item as CalendarDayItem);
					if(i > -1) {
						item.remove();
					} else { // search in full day items
						const i = this.viewModel.indexOf(item);
						if(i > -1) {
							item.remove();
						}
					}
				});
			}
		}).on('contextmenu', e =>{
			e.preventDefault();
			if(e.target.isA('dd')) { // CREATE
				const SNAP = client.user.calendarPreferences.weekViewGridSnap,
				 	liRect = this.el.lastElementChild!.lastElementChild!.getBoundingClientRect(),
					pxPerSnap = liRect.height / (1440 / SNAP), // 96 quarter-hours in a day
					minute = Math.round((e.clientY - liRect.top) / pxPerSnap) * SNAP;

				this.contextMenuEmpty.dataSet.date = (new DateTime(e.target.dataset.day!)).setHours(0, minute).format('c');
				this.contextMenuEmpty.showAt(e);
			}
		});

		return super.internalRender();
	}

	goto(day: DateTime, amount: number) {
		if(!day) {
			day = new DateTime();
		}

		this.days = amount;
		this.day = day.setHours(0,0,0,0);
		// const startWeek = this.day.setWeekDay(0),
		// 	endWeek = startWeek.clone().addDays(7);
		this.renderView();
		this.adapter.goto(day, day.clone().addDays(amount));
	}

	private makeDraggable() {

		let ev: CalendarItem,
			SNAP = client.user.calendarPreferences.weekViewGridSnap,
			changed: boolean,
			offset: number,
			last:number,
			lastDay:HTMLElement,
			anchor: number,
			from : number,
			till: number,
			currDayEl: HTMLElement,
			pxPerSnap: number,
			action: (m:number) => [number, number];

		const move: typeof action = m => [m, m+(ev.end.getMinuteOfDay()-ev.start.getMinuteOfDay())],
			resize: typeof action = m => (m > anchor) ? [anchor,m] : [m,anchor];

		const moveday = (day:HTMLElement) => {
			let [y,m,d] = day.dataset.day!.split('-').map(Number);
			ev.start.setYear(y).setMonth(m).setDate(d);
			ev.end = ev.start.clone().add(new DateInterval(ev.data.duration));
		};

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
					firstDiv.lastElementChild!.textContent = ev.start.format(Format.timeFormat) + ' - ' + ev.end.format(Format.timeFormat);
				changed = true;
				this.updateItems(ev.start.clone());
			}

		},
		mouseAllDayMove = (e: MouseEvent & {target: HTMLElement}) => {
			const day = e.target.up('li[data-day]');
			if(day && day !== lastDay) {
				changed = true;
				lastDay = day;
				moveday(day)
				Object.values(ev.divs).forEach(d => d.remove());
				ev.divs = {};
				this.updateFullDayItems();
			}
		},
		mouseUp = (_e:MouseEvent) => {
			this.el.cls('-resizing');
			this.el.un('mousemove', mouseMove);
			this.el.un('mousemove', mouseAllDayMove);
			changed && ev.save(() => {
				this.currentCreation = undefined;
				this.populateViewModel();
				//this.updateItems();
			});
		};

		this.el.on('mousedown', (e: MouseEvent) => {
			SNAP = client.user.calendarPreferences.weekViewGridSnap;
			changed = false;
			if (e.button !== 0) return;

			const liRect = this.el.lastElementChild!.lastElementChild!.getBoundingClientRect(),
				target = e.target as HTMLElement;
			pxPerSnap = liRect.height / (1440 / SNAP); // 96 quarter-hours in a day
			offset = liRect.top;
			currDayEl = target.up('[data-day]')!;
			const event = target.up('div[data-key]');
			if (event) { // MOVE
				offset += e.offsetY;
				ev = this.dayItems.find(m => m.key == event.dataset.key)!;
				if(!ev) {
					// find full day
					ev = this.viewModel.find(m => m.key == event.dataset.key)!;
					if(ev && ev.mayChange) {
						this.el.on('mousemove', mouseAllDayMove);
						this.el.cls('+resizing');
						window.addEventListener('mouseup', mouseUp,{once:true});
						return;
					}
				}
				if(!ev || !ev.mayChange) return;
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
				anchor = Math.round((e.clientY - offset) / pxPerSnap) * SNAP;
				const start = (new DateTime(target.dataset.day!)).setHours(0, anchor);
				const data = {
						start: start.format('Y-m-d\TH:i'),
						title: t('New event'),
						duration: client.user.calendarPreferences.defaultDuration ?? "PT1H",
						calendarId: CalendarView.selectedCalendarId,
						showWithoutTime: false
					},
					end = start.clone().addHours(1);

				this.currentCreation = ev = new CalendarDayItem({start,end,data,key:''});
				this.dayItems.unshift(ev as CalendarDayItem);
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
			withTime = [];
		if(this.currentCreation)
			withTime.unshift(this.currentCreation as CalendarDayItem);

		for(const item of this.adapter.items) {
			if(item.data?.showWithoutTime) {
				allDay.push(item);
			} else {
				withTime.push(item as CalendarDayItem);
			}
		}

		this.viewModel = allDay.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
		this.dayItems = withTime;
		this.updateFullDayItems();
		this.updateItems();
	}

	updateNowBar() {
		if(!this.nowbar) {
			return;
		}

		const now = new DateTime(), top = now.getMinuteOfDay() / 60, // 1296 = TOTAL HEIGHT of DAY
			left = 100 / this.days * (now.getWeekDay() - this.day.getWeekDay());
		this.nowbar.attr('style', `top: calc(var(--hour-height) * ${top});`);
		(this.nowbar.childNodes[1] as Element).attr('style', `left: ${left}%;`);
		(this.nowbar.childNodes[2] as Element).innerHTML = Format.time(now);
	}


	renderView() {
		const oldScrollTop = this.el.lastElementChild?.scrollTop;
		this.el.innerHTML = ''; // clear
		let now = new DateTime(),
			hour, day = this.day.clone();

		const hrs = now.clone();
		hrs.setMinutes(0);
		let heads = [], days = [],fullDays = [], hours = [], showNowBar=false;
		// const fnTime = /[Aa]$/.test(Format.timeFormat) ?  ((h:number) => h < 12 ? h+'am' : (h-12) + 'pm') : ((h: number) => h+':00');
		for (hour = 1; hour < 24; hour++) {
			hrs.setHours(hour)
			hours.push(E('em',hrs.format(Format.timeFormat)));
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
			const top = now.getMinuteOfDay() / 60, // 1296 = TOTAL HEIGHT of DAY
				left = 100 / this.days * (now.getWeekDay() - this.day.getWeekDay());
			this.nowbar = E('div', E('hr'),
				E('b').attr('style', `left: ${left}%;`),
				E('span', Format.time(now))
			).cls('now').attr('style', `top: calc(var(--hour-height) * ${top});`)
		} else {
			this.nowbar = undefined;
		}
		let ol: HTMLElement;

		this.el.append(
			E('ul',E('li',t('Wk')+' '+this.day.getWeekOfYear()).cls('current',showNowBar), ...heads),
			E('ul',E('li', t('All day')), this.alldayCtr = E('li').cls('all-days'), ...fullDays),
			ol = E('dl',E('dt', this.nowbar || '', E('em'), ...hours), ...days)
				.attr('style','--hour-height: '+(client.user.calendarPreferences.weekViewGridSize??8)+'vh')
		);
		setTimeout(() => ol.scrollTop = oldScrollTop || (ol.scrollHeight / 4)); // = scroll 6hours down (1/4 of day)
	}

	private updateFullDayItems() {

		this.slots = Array.from({length: this.days}, _ => ({}) );

		this.alldayCtr.innerHTML = '';
		this.alldayCtr.prepend(...this.viewModel.map(e =>
			this.drawEventLine(e, this.day)
			//super.eventHtml(e).css(this.makestyle(e, this.day))
		));

		var lengths = this.slots.map((i: any) => Object.keys(i).length);
		this.alldayCtr.parentElement!.style.height = (Math.max(...lengths,1) * this.ROWHEIGHT)+'rem';
	}

	private updateItems(day?: DateTime) {

		this.dayItems = this.dayItems.sort((a,b) => Math.sign(+a.start.date - +b.start.date));
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

	private drawDay(dayStart: DateTime, dayColumn: HTMLElement) {
		let stillContinueing = [],
			e: any,
			eventEls = [],
			end = dayStart.clone().addDays(1);
		outer: while((e = this.dayItems[this.iter]) && e.start.format('Ymd') < end.format('Ymd')) {
			this.iter++;

			// find same uid at same time to stack events
			let prev = this.continues.length-1;
			while((prev -= 1) >= 0 && e.start.getTime() === this.continues[prev].start.getTime()) {
				if(e.data.uid === this.continues[prev]?.data.uid) {
					this.continues[prev].calendarIds[e.data.calendarId] = true;
					continue outer;
				}
			}

			if(e.end.date > dayStart.date) {
				this.continues.push(e);
			}
		}
		if(this.continues.length)
			this.calculateOverlap(this.continues, dayStart);
		while(e = this.continues.shift()) {
			eventEls.push(this.drawEvent(e, dayStart, dayColumn));
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

		const [backgroundImage, paddingLeft] = this.stackedCalendars(e);

		if(!e.divs[i].isConnected)
			dd.append(e.divs[i]);
		return e.divs[i].css({
			color: '#'+e.color,
			top: (100 / 1440 * e.startM)+'%',
			left: (e.pos * (100 / e.lanes))+'%',
			width: 'calc('+(100 / e.lanes) +'% - 1px)',
			height: 'calc('+(100 / 1440 * (e.endM - e.startM))+'% - 1px)',
			backgroundImage, paddingLeft, borderLeft:'0'
		});
	}

	private stackedCalendars(e: CalendarDayItem) {
		let backgroundImage = 'linear-gradient(to right, ',
			padding = 0;

		for(const key in e.calendarIds) {
			if(key !== e.cal.id) {
				const cal = allCalendarStore.findById(key);
				if(cal) {
					backgroundImage += '#'+cal.color+' '+(padding/10)+'rem '+((padding+=6)/10)+'rem, ';
				}
			}
		}

		padding += 6;
		backgroundImage += 'currentColor '+((padding-6)/10)+'rem '+(padding/10)+'rem, transparent '+(padding/10)+'rem 100%)';
		return [backgroundImage, ((padding+6)/10)+'rem'];
	}

}