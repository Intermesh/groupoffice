import {CalendarView} from "./CalendarView.js";
import {DateTime, E, t} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarAdapter} from "./CalendarAdapter.js";

export class SplitView extends CalendarView {

	start!: DateTime
	calRows: [string, HTMLElement][] = []
	calViewModel : {[calId:string]: CalendarItem[]} = {}
	baseCls = 'cal split'

	constructor(adapter: CalendarAdapter) {
		super(adapter);
		calendarStore.on('load', () => { if(this.el.innerHTML !== '') {
			this.renderView()
			this.updateItems();
		} })
	}

	goto(day: DateTime, amount: number) {
		day ||= new DateTime();

		this.days = amount;
		this.day = day.setHours(0,0,0,0);
		this.start = this.day.clone().setWeekDay(0);
		const end = this.start.clone().addDays(amount);

		this.adapter.goto(this.start, end);
	}

	protected clear() {
		for(let calId in this.calViewModel) {
			this.calViewModel[calId].forEach(ev => {
				Object.values(ev.divs).forEach(d => d.remove());
			});
		}
		this.calViewModel = {};
	}

	protected populateViewModel() {
		this.clear()
		const viewEnd = this.start.clone().addDays(this.days);
		for (let calendar of calendarStore) {
			this.calViewModel[calendar.id] = [];
		}
		for (const e of this.adapter.items) {
			if(e.data.calendarId)
				this.calViewModel[e.data.calendarId].push(e);
		}
		for(let calId in this.calViewModel) {
			this.calViewModel[calId].sort((a,b) => a.start.date < b.start.date ? -1 : 1);
		}

		this.updateItems();
	}

	renderView() {
		this.el.innerHTML = ''; //clear
		let i = 0,
			now = new DateTime(),
			day = this.start.clone(); // toDateString removes time

		this.el.style.height = '100%';
		//this.el.cls(['+cal','+month']);
		const headDay = this.start.clone();
		const headers=[];
		for (i = 0; i < this.days; i++) {
			headers.push(E('li', headDay.format('D'), E('em', headDay.format('j'))).cls('today', headDay.format('Ymd') == now.format('Ymd')));
			headDay.addDays(1);
		}
		this.el.append(E('ul', ...headers)); // headers

		this.calRows = [];
		for (let calendar of calendarStore) {
			if(!calendar.isVisible) continue;
			day = this.start.clone();
			const eventContainer = E('li', ...this.drawCal(calendar.id)).cls('events'),
				row = E('ol',
					eventContainer,
					//E('li', E('i', 'event').cls('icon').css({color:'#'+calendar.color}), calendar.name)
				);
			for (i = 0; i < this.days; i++) {
				row.append(E('li').attr('data-date', day.format('Y-m-d'))
					.cls('today', day.format('Ymd') === now.format('Ymd'))
					.cls('past', day.format('Ymd') < now.format('Ymd'))
					.cls('other', day.format('Ym') !== this.day.format('Ym')))

				day.addDays(1);
				//it++;
			}
			this.calRows.push([calendar.id, eventContainer]);
			this.el.append(E('div',E('i', 'event').cls('icon').css({color:'#'+calendar.color}), calendar.name), row);
		}

	}

	private updateItems() {

		for(const [calId, container] of this.calRows) {
			this.iterator = 0;
			container.append(...this.drawCal(calId));
		}
		// call draw week but re-use divs only set style ignore the return value
	}

	private drawCal(calId: string) {
		const wstart = this.start.clone();
		let end = this.start.clone().addDays(this.days),
			e: any;
		let eventEls = [];
		this.slots = {0:{},1:{},2:{},3:{},4:{},5:{},6:{}};

		while(true) {
			const e = this.calViewModel[calId] && this.calViewModel[calId][this.iterator];
			if(!e || e.start.format('YW') > end.format('YW')) {
				break;
			}
			eventEls.push(this.drawEvent(e, wstart));
			this.iterator++;
		}

		return eventEls;
	}

	iterator!: number

	drawEvent(e: CalendarItem, weekstart: DateTime) {
		if(!e.divs[weekstart.format('YW')]) {
			e.divs[weekstart.format('YW')] = super.eventHtml(e);
		}
		return e.divs[weekstart.format('YW')]
			.css(this.makestyle(e, weekstart))
	}
}