import {Component} from "@goui/component/Component";
import {jmapstore} from "@goui/jmap/JmapStore.js";
import {Recurrence} from "./Recurrence.js";
import {E} from "@goui/util/Element.js";
import {DateTime} from "@goui/util/DateTime.js";
import {t} from "@goui/Translate.js";

export interface CalendarEvent {
	recurrenceRule : any
	links: any
	alerts: any
	showWithoutTime: boolean // isAllDay
	duration: string
	id: string
	start: string
	title: string
	calendarId: string
}

export abstract class CalendarView extends Component {
	
	protected day: DateTime = new DateTime()
	protected days: number = 1
	protected firstDay?: DateTime
	protected recur?: {[id:string]: Recurrence}


	protected store: any

	constructor() {
		super();
		this.store = jmapstore({
			entity:'CalendarEvent',
			properties: ['title', 'start','duration','calendarId','showWithoutTime','alerts','recurrenceRule','id'],
			listeners: {'load': (me,records) => this.update()}
		})
		this.on('render', () => { this.store.load() });
	}


	private expandRecurrence() {
		// todo: move to store update (only for new events)
		let recur: any = {};
		// for (var i=0,e; e = this.store.get(this.query.ids[i]); i++) {
		// 	if(e.recurrenceRule) {
		// 		recur[e.id] = new Recurrence({dtstart: new Date(e.start), rule: e.recurrenceRule, ff: this.firstDay});
		// 	}
		// }
		
		return recur;
	}

	 internalRender(){
	 	// this.goto(new DateTime());
	// 	dom.on('click',(ev) => {
	//
	// 		const event = ev.target.up('.event');
	// 		if(event && !event.has('.moving')) {
	// 			const dlg = new EventDialog();
	// 			dlg.show().form.load(event.dataset.id);
	//
	// 		}
	// 		// const day = ev.target.up('li[data-date]');
	// 		// if(day) {
	// 		// 	const dlg = new EventDialog();
	// 		// 	//const date = Date.fromYmd(day.dataset.date);
	// 		// 	dlg.show(event).form.create({start: day.dataset.date, end: day.dataset.date})
	//
	// 		// }
	// 		ev.preventDefault();
	// 	});
		 return this.el;
	 }

	update = (data?: any) => {
		//this.fire('change', data);
		//this.dom.cls('-loading');
		//if(this.isRendered()) {
			this.recur = this.expandRecurrence();
			this.renderView();
		//}
	}

	protected eventHtml(e: CalendarEvent, style: string) {
		const items = [],
			 start = new DateTime(e.start);
		if(e.recurrenceRule) items.push('refresh');
		if(e.links) items.push('attachment');
		if(e.alerts) items.push('notifications');
		items.push(e.title || '('+t('Nameless')+')');
		if(e.showWithoutTime)
			items.push(E('span', start.format('H:i')+' - '+start.addDuration(e.duration).format('H:i')));
		return E('div', ...items).cls('event').attr('data-id', e.id).attr('style',style);
	}

	protected calculateOverlap(events: any[]) {
		let overlap: any = {}; // clear

		// read start, end, span, max
		for (const event of events) {
			let start = new DateTime(event.start),
				startM = start.getHours()*60+start.getMinutes(),
				end = start.clone().addDuration(event.duration),
				endM = end.format('Ymd') > start.format('Ymd') ? 1450 : end.getHours()*60+end.getMinutes();

			overlap[event.id] = {start: startM, end: endM,span: 1, max:1};
		}
		
		// count max overlap
		for(const me in overlap) {
			const o = overlap[me];
			for(const id in overlap) {
				const ov = overlap[id];
				if((o.start < ov.end && o.start > ov.start) || 
					(o.end > ov.start && o.end < ov.end)) {
					o.max++;
				} else if (ov.start > o.end) break;
			}
		}
		
		let position = 0,
			prevMax=1,
			previousCols:any = {};

		// set col and colspan
		for (const event of events) {
			const o = overlap[event.id];
			
			let col = position % prevMax;
			
			if(col+1 == prevMax)
				position=0;
			
			let pcol = col = position % prevMax,
				 ppos = position;
			
			while (pcol != col || ppos==position && ppos<6) { // why 6?
				ppos++;
				if(!previousCols[pcol]) {
					pcol = ppos % prevMax;
					continue;
				}
				const previous = overlap[previousCols[pcol].id];

				//collision detection
				if(previous.end > o.start && pcol == col) {
					position++;
					col = position % prevMax;
					//o.max = Math.max(o.max,previous.max);
					previous.max = Math.max(o.max,previous.max);
					
				}
				else if(previous.end > o.start) {
					o.max = Math.max(o.max,previous.max);
				}
				
				pcol = ppos % prevMax;
			}

			o.col = col;

			previousCols[position % o.max] = event;
			prevMax = o.max;
		}

		return overlap;
	}

	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void
}