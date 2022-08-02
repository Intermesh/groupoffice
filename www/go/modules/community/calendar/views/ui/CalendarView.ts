abstract class CalendarView extends Component { 
	
	protected day: Date = new Date()
	protected firstDay?: Date
	protected recur?: {[id:string]: Recurrence}

	protected store!: Store
	protected query!: ServerQuery

	constructor(cfg: ComponentCfg) {
		super(cfg);
		this.cls = 'anim horizontal';
		this.bind('CalendarEvent');
	}

	private bind(name: string) {
		this.store = $.db.store(name); // move to query?
		this.query = this.store.query('month').on('update', this.update) as ServerQuery;
	}

	private expandRecurrence() {
		// todo: move to store update (only for new events)
		let recur: any = {};
		for (var i=0,e; e = this.store.get(this.query.ids[i]); i++) {
			if(e.recurrenceRule) {
				recur[e.id] = new Recurrence({dtstart: new Date(e.start), rule: e.recurrenceRule, ff: this.firstDay});
			}
		}
		
		return recur;
	}

	onRender(dom){
		this.setDate(new Date());
		dom.on('click',(ev) => {

			const event = ev.target.up('.event');
			if(event && !event.has('.moving')) {
				const dlg = new EventDialog();
				dlg.show(event).form.load(event.dataset.id);
				
			}
			// const day = ev.target.up('li[data-date]');
			// if(day) {
			// 	const dlg = new EventDialog();
			// 	//const date = Date.fromYmd(day.dataset.date);
			// 	dlg.show(event).form.create({start: day.dataset.date, end: day.dataset.date})
				
			// }
			ev.preventDefault();
		});
	}

	update = (data?: any) => {
		//this.fire('change', data);
		//this.dom.cls('-loading');
		if(this.isRendered()) {
			this.recur = this.expandRecurrence();
			this.renderView();
		}
	}

	protected eventHtml(e, style) {
		return `<div data-id="${e.id}" style="${style}" class="event">
			${e.recurrenceRule ? '<i>refresh</i>':''}
			${e.links ? '<i>attachment</i>':''}
			${e.alerts ? '<i>notifications</i>':''}
			${e.title || '('+t('Nameless')+')'}
			${!e.isAllDay ? '<span>'+(e.start && e.start.date().to('H:i'))+' - '+e.start.date().addDuration(e.duration).to('H:i')+'</span>':''}
		</div>`;
	}

	protected calculateOverlap(events: any[]) {
		let overlap: any = {}; // clear

		// read start, end, span, max
		for (const event of events) {
			let start = event.start.date(),
				startM = start.getHours()*60+start.getMinutes(),
				end = start.clone().addDuration(event.duration),
				endM = end.to('Ymd') > start.to('Ymd') ? 1450 : end.getHours()*60+end.getMinutes();

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

	abstract setDate(day:any): void
}