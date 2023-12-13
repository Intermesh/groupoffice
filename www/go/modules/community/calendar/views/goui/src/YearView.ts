import {CalendarView} from "./CalendarView.js";
import {ComponentEventMap, DateTime, E, ObservableListenerOpts} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";

export interface YearViewEventMap<Type> extends ComponentEventMap<Type> {

	weekclick: (me: Type, week: any) => void
	monthclick: (me: Type, month: any) => void
	dayclick: (me: Type, day: any) => void

}


export interface YearView extends CalendarView {
	on<K extends keyof YearViewEventMap<this>, L extends Function>(eventName: K, listener: Partial<YearViewEventMap<this>>[K], options?: ObservableListenerOpts): L;
	fire<K extends keyof YearViewEventMap<this>>(eventName: K, ...args: Parameters<YearViewEventMap<any>[K]>): boolean
}

export class YearView extends CalendarView {

	baseCls = 'yearview'

	goto(date: DateTime, days: number) {
		this.day= date.clone().setDate(1).setMonth(1); // 1st jan;
		const endYear = this.day.clone().addYears(1);
		Object.assign(this.store.queryParams.filter ||= {}, {
			after: this.day.format('Y-m-d'),
			before: endYear.format('Y-m-d')
		});

		this.store.load()
		//this.populateViewModel();
	}

	update = (data?: any) => {
		if(this.rendered) {
			//this.renderView(); this will be done in populateViewModel() for this view
			this.populateViewModel();
		}
	}

	protected populateViewModel() {
		this.clear()
		const viewEnd = this.day.clone().addYears(1);
		for (const e of this.store.items) {
			this.viewModel.push(...CalendarItem.makeItems(e, this.day, viewEnd));
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
	renderMonth(m:number, day:DateTime) {
		const monthDay = day.clone();
		var now = new DateTime(),
			caption = E('caption', DateTime.monthNames[m-1])
				.cls('current', day.format('mY') == now.format('mY'))
				.attr('data-month', m)
				.on('click', ev =>  {
					this.fire('monthclick', this, monthDay)
				} );

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
				row = E('tr');
				if(+day.format('m') === m) {
					row.append(E('td', weekDay.getWeekOfYear()).cls('weeknb').on('click', ev => {
						this.fire('weekclick', this, weekDay);
					}))
				}
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
					.attr('title', e.title+' - '+e.start.format('H:i'))
					.css({backgroundColor: '#'+e.color})
				);
			}
			const cDay = day.clone();
			const td = E('td').on('click',ev => {
				this.fire('dayclick', this, cDay);
			});
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