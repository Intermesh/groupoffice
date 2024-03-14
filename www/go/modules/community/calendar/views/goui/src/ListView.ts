import {CalendarView} from "./CalendarView.js";
import {DateTime, E, splitter, t} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {EventDetail} from "./EventDetail.js";

export class ListView extends CalendarView {

	start!: DateTime
	continues?: CalendarItem[]
	iterator?:number
	listEl: HTMLUListElement

	detail: EventDetail

	constructor(adapter: CalendarAdapter) {
		super(adapter);
		this.cls = 'hbox';
		this.el.append(this.listEl = E('ul'));
		this.detail = new EventDetail();
		this.items.add(
			splitter({resizeComponentPredicate: this.detail}),
			this.detail
		);
	}

	goto(date: DateTime, days: number): void {
		this.day = date.setHours(0,0,0, 0);
		this.start = date.clone()
		const endMonth = this.start.clone();

		this.start.setDate(1);
		endMonth.setDate(1).addMonths(1);
		this.days = this.start.diff(endMonth).getTotalDays()!;

		this.adapter.goto(this.start, endMonth);
	}

	protected populateViewModel(): void {
		this.clear()

		for(const item of this.adapter.items) {
			this.viewModel.push(item);
		}

		this.updateItems()
	}

	renderView(): void {
		this.viewModel = [];
		this.listEl.innerHTML = ''; //clear

		this.listEl.cls(['+cal','+list']).css({flex:'1'});

	}

	private updateItems() {
		this.continues = [];
		this.iterator = 0;
		this.viewModel.sort((a, b) => a.start.date < b.start.date ? -1 : 1);

		const day = this.start.clone();
		const continues = this.continues;
		this.continues = [];
		let row, e,
			ce;
		for (let i = 0; i < 42; i++) {
			row = E('li', E('h3',E('em', day.format('j')), day.format('M, D')));

			while (ce = continues.shift()) {
				this.drawItem(ce, row, day);
			}
			while ((e = this.viewModel[this.iterator])) {
				if (this.drawItem(e, row, day) === false) {
					break;
				}
				this.iterator++;
			}
			row.cls('empty', row.children.length === 1)
			this.listEl.append(row);

			day.addDays(1);
			if (day.format('Ym') > this.day.format('Ym')) {
				break;
			}
		}
	}

	private drawItem(e:CalendarItem, container: HTMLElement, day:DateTime) {
		if(e.start.format('Ymd') > day.format('Ymd')) {
			return false; // event is in future
		}
		if(e.end.date < day.date) {
			return; // ff
		}

		const time = E('span', e.data.showWithoutTime ? t('Full day') :
			e.start.format('G:i') + e.end.format(' - G:i')),
			title = E('span', e.title);
		e.divs[0] = super.eventHtml(e,E('div',
			E('i','fiber_manual_record').cls('icon'),
			time,
			title
		).css({color: '#'+e.color})).on('click', () => {
			this.detail.loadEvent(e);
		});
		container.append(e.divs[0]);

		if(e.end.date > day.date) {
			this.continues!.push(e); // continues next day
		}
	}
}