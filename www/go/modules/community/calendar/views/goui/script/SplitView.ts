import {CalendarView} from "./CalendarView.js";
import {DateTime, E} from "@intermesh/goui";
import {MonthView} from "./Index.js";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {jmapds} from "@intermesh/groupoffice-core";

export class SplitView extends MonthView {

	start!: DateTime
	calRows: [string, HTMLElement][] = []
	calViewModel : {[calId:string]: CalendarItem[]} = {}
	baseCls = 'cal split'
	calendars: any[] = []
	constructor(adapter: CalendarAdapter) {
		super(adapter);
	}

	protected internalRender() {

		this.el.on('mousedown', (e) => {
			const found = e.target.up('ol[data-calid]', this.el);
			if(found) {
				this.setActiveCalendar(found.dataset.calid!);
			}
		});

		return super.internalRender();
	}

	private setActiveCalendar(id: string) {
		CalendarView.selectedCalendarId = id;
		this.viewModel = this.calViewModel[id];
	}


	goto(day: DateTime, amount: number) {
		day ||= new DateTime();

		this.wdays = amount;
		this.day = day.setHours(0,0,0,0);
		this.start = this.day.clone().setWeekDay(0);
		const end = this.start.clone().addDays(amount);

		this.renderView();
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

		const activeFilter = this.adapter.byType('event').store.queryParams.filter.inCalendars;
		//const viewEnd = this.start.clone().addDays(this.wdays);
		for (let calendarId of activeFilter) {
			this.calViewModel[calendarId] = [];
		}
		for (const e of this.adapter.items) {
			if(e.data.calendarId && this.calViewModel[e.data.calendarId])
				this.calViewModel[e.data.calendarId].push(e);
		}
		for(let calId in this.calViewModel) {
			if(this.calViewModel[calId])
				this.calViewModel[calId].sort((a,b) => a.start.date < b.start.date ? -1 : 1);
		}

		this.updateItems();
	}

	renderView() {
		this.el.innerHTML = ''; //clear
		let i,
			now = new DateTime(),
			day = this.start.clone(); // toDateString removes time

		this.el.style.height = '100%';
		//this.el.cls(['+cal','+month']);
		const headDay = this.start.clone();
		const headers=[];
		for (i = 0; i < this.wdays; i++) {
			headers.push(E('li', headDay.format('D'), E('em', headDay.format('j'))).cls('today', headDay.format('Ymd') == now.format('Ymd')));
			headDay.addDays(1);
		}
		this.el.append(E('ul', ...headers)); // headers

		this.calRows = [];
		const activeFilter = this.adapter.byType('event').store.queryParams.filter.inCalendars;
		jmapds('Calendar').get(activeFilter).then((resp) => {
			this.calendars = resp.list;
			for (let calendar of this.calendars) {

				if (activeFilter.indexOf(calendar.id) === -1) continue;
				day = this.start.clone();
				const eventContainer = E('li').cls('events'),
					row = E('ol',
						eventContainer,
						//E('li', E('i', 'event').cls('icon').css({color:'#'+calendar.color}), calendar.name)
					).attr('data-calid', calendar.id);
				for (i = 0; i < this.wdays; i++) {
					row.append(E('li').attr('data-date', day.format('Y-m-d'))
						.cls('today', day.format('Ymd') === now.format('Ymd'))
						.cls('past', day.format('Ymd') < now.format('Ymd'))
						.cls('other', day.getDay() % 6 == 0))

					day.addDays(1);
					//it++;
				}
				this.calRows.push([calendar.id, eventContainer]);
				this.el.append(E('div', E('i', 'event').cls('icon').css({color: '#' + calendar.color}), calendar.name), row);
			}
			this.populateViewModel();
		});
	}

	protected updateItems() {

		for(const [calId, container] of this.calRows) {
			this.weekRows = [[this.start.clone(), container]];
			this.viewModel = this.calViewModel[calId];
			super.updateItems();
			//container.append(...this.drawCal(calId));
		}
		// call draw week but re-use divs only set style ignore the return value
	}
}