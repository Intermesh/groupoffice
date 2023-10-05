import {CalendarView} from "./CalendarView.js";
import {DateTime, E} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {Main} from "./Main.js";

export class YearView extends CalendarView {
	main: Main
	constructor(main: Main) {
		super();
		this.main = main;
		this.cls = 'yearview';
	}

	protected populateViewModel() {
		this.clear()
		const viewEnd = this.day.clone().addYears(1);
		for (const e of this.store.items) {
			this.viewModel.push(...super.makeItems(e, this.day, viewEnd));
		}
		this.viewModel.sort((a,b) => a.start.date < b.start.date ? -1 : 1);

		this.renderView()
	}

	goto(date: DateTime, days: number) {
		this.day= date.clone().setDate(1).setMonth(1); // 1st jan;
		this.populateViewModel();
	}

	renderView() {
		this.iterator = 0;
		this.el.innerHTML = '';
		let start = this.day.clone();
		for(var m = 1; m <= 12; m++) {
			this.renderMonth(m, start);
		}
	}

	iterator!: number
	renderMonth(m:number, day:DateTime) {
		var now = new DateTime(),
			caption = E('caption', DateTime.monthNames[m-1])
				.cls('current', day.format('mY') == now.format('mY'))
				.attr('data-month', m)
				.on('click', ev => this.main.goto(day).setSpan('month', 31));

		const header = E('tr',E('td'));
		for(let i=0;i < 7;i++) {
			header.append(E('th',Object.values(DateTime.dayNames)[i][0]))
		}
		const rows = [];
		day.setDate(1).setWeekDay(0);
		let row,e;
		for (let i = 0; i < 42; i++) {
			if (i % 7 == 0){
				const weekDay = day.clone();
				row = E('tr',
					E('td', weekDay.getWeekOfYear()).cls('weeknb').on('click', ev => {
						this.main.goto(weekDay).setSpan('week', 7);
					})
				);
				rows.push(row);
			}
			const ev = E('div').cls('events');
			while(e = this.viewModel[this.iterator]) {
				//console.log(e.start.format('Ymd'), day.format('Ymd'));
				if(e.start.format('Ymd') > day.format('Ymd')) {
					break;
				}
				this.iterator++;
				if(e.start.format('Ymd') < day.format('Ymd')) { // ff
					continue;
				}
				ev.append(E('p')
					.attr('title', e.data.title+' - '+e.start.format('H:i'))
					//.attr('data-color', e.color)
					.css({backgroundColor: '#'+e.color})
				);
			}
			const td = E('td');
			if(+day.format('m') === m) {
				td.cls('today', day.format('Ymd') === now.format('Ymd'))
					.cls('past', day.format('Ymd') < now.format('Ymd'))
					.append(E('span', day.getDate()), ev)
			}
			row!.append(td);
			day.addDays(1);
		}

		this.el.append(E('div',E('table',
			caption,
			header,
			...rows
		)));
	}
}