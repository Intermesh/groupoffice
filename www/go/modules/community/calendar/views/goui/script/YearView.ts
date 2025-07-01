import {CalendarView} from "./CalendarView.js";
import {ComponentEventMap, DateTime, E, Format, ObservableListenerOpts} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {client} from "@intermesh/groupoffice-core";

export interface YearViewEventMap extends ComponentEventMap {
	weekclick: { week: any}
	monthclick: {month: any}
	dayclick: {day: any}
}

export class YearView extends CalendarView<YearViewEventMap> {

	baseCls = 'yearview'

	goto(date: DateTime, days: number) {
		this.day= date.clone().setDate(1).setMonth(1).setHours(12,0,0,0); // 1st jan;
		const endYear = this.day.clone().addYears(1);

		this.adapter.goto(this.day, endYear)
	}

	update = (data?: any) => {
		if(this.rendered) {
			//this.renderView(); this will be done in populateViewModel() for this view
			this.populateViewModel();
		}
	}

	protected populateViewModel() {
		this.clear()
		for(const item of this.adapter.items){
			this.viewModel.push(item);
		}
		this.renderView()
	}

	renderView() {
		this.viewModel.sort((a,b) => a.start.date < b.start.date ? -1 : 1);
		this.iterator = 0;
		this.el.innerHTML = '';
		let d = this.day.clone();
		for(let m = 1; m <= 12; m++) {
			this.renderMonth(m, d);
		}
	}

	iterator!: number

	continues: CalendarItem[] = []

	renderMonth(m:number, day:DateTime) {
		const monthDay = day.clone();
		var now = new DateTime(),
			caption = E('caption', DateTime.monthNames[m-1])
				.cls('current', day.format('mY') == now.format('mY'))
				.attr('data-month', m)
				.on('click', ev =>  {
					this.fire('monthclick', {month: monthDay})
				});

		const header = E('tr', client.user.calendarPreferences.showWeekNumbers ?E('td') : '');
		for(let i=0;i < 7;i++) {
			header.append(E('th',Object.values(DateTime.dayNames)[i][0]))
		}
		const rows = [];
		day.setDate(1).setWeekDay(0);
		let row,
			e,
			ce;
		for (let i = 0; i < 42; i++) {
			if (i % 7 == 0){
				const weekDay = day.clone();
				row = E('tr');
				//if(+day.format('m') === m) {
				if(client.user.calendarPreferences.showWeekNumbers) {
					row.append(E('td', weekDay.getWeekOfYear()).cls('weeknb').on('click', ev => {
						this.fire('weekclick', {week: weekDay});
					}))
				}

				rows.push(row);
			}

			const evContainer = E('div').cls('events');
			const continues = this.continues;
			this.continues = [];
			while (ce = continues.shift()) {
				this.drawDot(ce, evContainer, day);
			}
			while ((e = this.viewModel[this.iterator])) {
				if (this.drawDot(e, evContainer, day) === false) {
					break;
				}
				this.iterator++;
			}

			const cDay = day.clone();
			const td = E('td').on('click',ev => {
				this.fire('dayclick', {day:cDay});
			});
			if(+day.format('m') === m) {
				td.cls('today', day.format('Ymd') === now.format('Ymd'))
					.cls('past', day.format('Ymd') < now.format('Ymd'))
					.append(E('span', day.getDate()), evContainer)
			}
			row!.append(td);

			day.addDays(1);
			if(day.format('Ym') > this.day.format('Y')+(m+"").padStart(2,'0')) {
				break;
			}
		}

		this.el.append(E('div',E('table',
			caption,
			header,
			...rows
		)));
	}

	private drawDot(e:CalendarItem, container: HTMLElement, day:DateTime) {
		if(e.start.format('Ymd') > day.format('Ymd')) {
			return false; // event is in future
		}
		if(e.end.date < day.date) {
			return; // ff
		}
		container.append(E('p')
			.attr('title', e.title+' - '+Format.time(e.start))
			.css({backgroundColor: '#'+e.color})
		);

		if(e.end.date > day.date) {
			this.continues.push(e); // continues next day
		}
	}
}