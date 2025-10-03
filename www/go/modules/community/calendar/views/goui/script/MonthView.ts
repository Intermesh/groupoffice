import {CalendarView} from "./CalendarView.js";
import {ComponentEventMap, DateInterval, DateTime, ObservableListenerOpts} from "@intermesh/goui";
import {E} from "@intermesh/goui";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {client} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

// add selectweek event

export interface MonthViewEventMap extends ComponentEventMap {
	selectweek: {day: DateTime}
	dayclick: { day: any}
}

export class MonthView extends CalendarView<MonthViewEventMap> {

	start!: DateTime
	dragData?: CalendarEvent

	weekRows: [DateTime, HTMLElement][] = []

	protected internalRender() {
		this.makeDraggable(this.el);

		this.el.tabIndex = 0;
		this.el.on('keydown', (e: KeyboardEvent) => {
			if(e.key == 'Delete') {
				this.selected.forEach(item => {
					const i = this.viewModel.indexOf(item);
					if(i > -1) {
						item.remove();
					}
				});
			}
		}).on('contextmenu', e =>{
			e.preventDefault();
			const day = e.target.up('li[data-date]');
			if(day) {
				this.contextMenuEmpty.dataSet.date = day.dataset.date;
				this.contextMenuEmpty.showAt(e);
			}
		});
		const observer = new ResizeObserver(entries => {
			if(this.weekRows.length) {
				for (let entry of entries) {
					//const height = (entry.contentRect.height - entry.target.firstElementChild!.clientHeight) / this.weekRows.length;

					this.updateHasMore();
					//entry.contentRect.height / this.weekRows.length;
					//console.log(entry.contentRect.height, this.weekRows.length);
					//console.log("Size changed:", entry.contentRect.width, entry.contentRect.height);
				}
			}

		});
		observer.observe(this.el);

		return super.internalRender();
	}

	goto(date: DateTime, days?: number) {
		//this.el.cls('reverse',(day < this.day));
		this.day = date.setHours(0,0,0, 0);
		this.start = date.clone()
		const endMonth = this.start.clone();
		if(days) {
			this.start.setWeekDay(0);
			this.days = days;
			endMonth.addDays(days);
		} else { // take full month
			this.start.setDate(1).setWeekDay(0);
			endMonth.setDate(1).setWeekDay(0).addDays(6*7);
			this.days = this.start.diff(endMonth).getTotalDays()!;
		}
		this.renderView();
		this.adapter.goto(this.start, endMonth);
	}

	private makeDraggable(el: HTMLElement) {
		let from : HTMLElement,
			till: HTMLElement,
			last: HTMLElement,
			anchor: HTMLElement,
			currentH = (new DateTime).format('H'),
			ev: CalendarItem,
			action: (day:HTMLElement) => void,
			hasMoved= false;

		const create = (day: HTMLElement) => {
			[from, till] = (anchor.compareDocumentPosition(day) & 0x02) ? [day,anchor] : [anchor,day];

			ev.data.showWithoutTime = from !== till || !client.user.calendarPreferences.defaultDuration;
			if(ev.data.showWithoutTime) {
				ev.start = new DateTime(from.dataset.date!+' 00:00:00.000');
				ev.end = new DateTime(till.dataset.date!+' 00:00:00.000').addDays(1);
			} else {
				const time = ' '+currentH+':00:00.000';
				ev.start = new DateTime(from.dataset.date!+time);
				ev.end = new DateTime(till.dataset.date!+time).add(new DateInterval(ev.data.duration));
			}
		},
		move = (day:HTMLElement) => {
			let [y,m,d] = day.dataset.date!.split('-').map(Number);
			ev.start.setYear(y).setMonth(m).setDate(d);
			ev.end = ev.start.clone().add(new DateInterval(ev.data.duration));
		},
		mouseMove = (e: MouseEvent & {target: HTMLElement}) => {

			const day = e.target.up('li[data-date]');
			if(day && day != last) {
				hasMoved = true;
				last = day;
				action(day)
				Object.values(ev.divs).forEach(d => d.remove());
				ev.divs = {};
				this.updateItems();
			}
		},
		mouseUp = (_e: MouseEvent) => {
			el.un('mousemove', mouseMove);
			(hasMoved || action === create) && ev.save( () => {
				this.currentCreation = undefined;
				this.populateViewModel();
			});
		};
		el.on('mousedown', (e) => {
			if(e.button !== 0) return;
			e.preventDefault(); // no text selection
			const day = e.target.up('li[data-date]');
			if(day) {
				const dd = client.user.calendarPreferences.defaultDuration,
					startStr = day.dataset.date! + (dd ? (new DateTime).format(' H:00:00.000') : ' 00:00:00.000');
				const data = {
						start: startStr,
						title: t('New event'),
						duration: dd ?? 'P1D',
						calendarId: CalendarView.selectedCalendarId,
						showWithoutTime: !dd
					},
					start = (new DateTime(data.start));
				this.currentCreation = ev = new CalendarItem({start, data, key: ''});
				this.viewModel.unshift(ev);
				this.updateItems();
				//this.drawEvent(ev, weekStart);
				//eventsContainer.prepend(ev.divs[0]);
				anchor = from = till = day;
				action = create;
				el.on('mousemove', mouseMove);
				window.addEventListener('mouseup', mouseUp, {once:true});
			}
			const event = e.target.up('div[data-key]');
			if(event) {
				ev = this.viewModel.find(m => m.key == event.dataset.key)!;
				if(!ev || !ev.mayChange) return;
				action = move;
				el.on('mousemove', mouseMove);
				window.addEventListener('mouseup', mouseUp, {once:true});
			}
		});
	}

	protected clear() {
		this.viewModel.forEach(ev => {
			Object.values(ev.divs).forEach(d => d.remove());
		})
		this.viewModel = [];
	}

	protected populateViewModel() {
		this.clear()

		for(const item of this.adapter.items) {
			this.viewModel.push(item);
		}
		if(this.currentCreation)
			this.viewModel.unshift(this.currentCreation);
		this.updateItems();
		this.updateHasMore();
	}

	renderView() {
		this.viewModel = [];
		this.el.innerHTML = ''; //clear
		let it = 0, i =0;
		let now = new DateTime(),
			 day = this.start.clone(); // toDateString removes time

		this.el.style.height = '100%';
		this.el.cls(['+cal','+month']);
		this.el.append(E('ul',...Object.values(DateTime.dayNames).map((name,i) =>
			E('li',name.substring(0,2)).cls('current', this.day.format('Ym') == now.format('Ym') && now.getWeekDay() == i)
		))); // headers

		this.weekRows = [];
		while (it < this.days) {
			const weekStart = day.clone(),
				eventContainer = E('li').cls('events'),
				row = E('ol',eventContainer);
			for (i = 0; i < 7; i++) {
				const cDay = day.clone();
				row.append(E('li',
					(i==0 && client.user.calendarPreferences.showWeekNumbers) ? E('sub','W '+day.getWeekOfYear()).cls('weeknb').cls('not-small-device')
						.on('click',_e => this.fire('selectweek', {day: weekStart}))
						.on('mousedown',e=>e.stopPropagation()):'',
					E('span',E('em', day.format( 'j')), day.format( day.getDate() === 1 ?' M' :'')).on('click', _e => {
						this.fire('dayclick', {day: cDay});
					}).on('mousedown', e => { e.stopPropagation()}),
					E('div','+ 0 more').cls('more').on('click', _e => {
						this.fire('dayclick', {day: cDay});
					}).on('mousedown',e=>e.stopPropagation())
				).attr('data-date', day.format('Y-m-d'))
				 .cls('today', day.format('Ymd') === now.format('Ymd'))
				 .cls('past', day.format('Ymd') < now.format('Ymd'))
				 .cls('other', day.format('Ym') !== this.day.format('Ym'))
				 .cls('weekend',day.getDay()===0 || day.getDay()===6))
				day.addDays(1);
				it++;
			}
			this.weekRows.push([weekStart, eventContainer]);
			this.el.append(row);
		}

	}


	private updateHasMore() {
		// height of the week row
		const height = (this.el.clientHeight - this.el.firstElementChild!.clientHeight) / this.weekRows.length;
		// how many event fit in the week row (todo: rem to pix != /10)
		const fit = Math.floor((height/10) / this.ROWHEIGHT);

		const ols = this.el.getElementsByTagName('ol');
		for (let i = 0; i < ols.length; i++) {
			const lis = ols[i].getElementsByTagName('li');
			for (let j = 0; j < lis.length; j++) {
				const li = lis[j];
				if(li.hasAttribute('amount')) {
					const moreCount = (+li.getAttribute('amount')! - fit);
					li.cls('showMore', moreCount > 0);
					li.lastElementChild!.innerHTML = t('+ {n} more').replace('{n}', moreCount) ;
				}
			}
		}
	}

	private updateItems() {
		this.continues = [];
		this.iterator = 0;
		this.viewModel.sort((a,b) => a.start.date < b.start.date ? -1 : 1);
		for(const [ws, container] of this.weekRows) {
			container.append(...this.drawWeek(ws));
			this.slots.forEach((v, i) => {
				container.parentElement!.children[i+1]!.setAttribute('amount', ''+Object.keys(v).length);
			})
		}

		// call draw week but re-use divs only set style ignore the return value
	}

	iterator!: number
	continues: CalendarItem[] = []

	private drawWeek(wstart: DateTime) {
		let end = wstart.clone().addDays(7),
			e: any;
		let eventEls = [];
		this.slots = [{},{},{},{},{},{},{}];
		let stillContinueing = [];
		while(e = this.continues.shift()) {
			eventEls.push(this.drawEventLine(e, wstart));
			if(e.end.date > end.date) {
				stillContinueing.push(e); // push it back for next week
			}
		}
		this.continues = stillContinueing;

		while((e = this.viewModel[this.iterator]) && e.start.format('Ymd') < end.format('Ymd')) {
			eventEls.push(this.drawEventLine(e, wstart));
			if(e.end.date > end.date) {
				this.continues.push(e);
			}
			this.iterator++;
		}
		return eventEls;
	}


}