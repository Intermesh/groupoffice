import {CalendarView} from "./CalendarView.js";
import {DateTime} from "@goui/util/DateTime.js";
import {E} from "@goui/util/Element.js";

export class MonthView extends CalendarView {

	setDate(day: DateTime) {
		if(!day) {
			day = (new DateTime()).setDate(1);
		}
		//this.el.cls('reverse',(day < this.day));
		this.day = day.clone();
		let end = day.clone();
		day.setDate(1).setWeekDay(0);
		this.firstDay = day.clone();
		end.addMonths(1).setDate(0).setWeekDay(6).addDays(1); //end sunday after last day
		//this.renderView();
		//this.dom.cls('+loading');
		//this.store.filter('date', {after: day.format('Y-m-dT00:00:00'), before: end.format('Y-m-dT00:00:00')}).fetch(0,500);
	}

	renderView() {
		let it = 0;
		let now = new DateTime(),
			 day = this.day.clone(); // toDateString removes time
		day.setDate(1);
		let start = day.clone(), e;

		this.el.style.height = '100%';
		this.el.cls(['+cal','+month','+active']);
		this.el.append(E('ul',...DateTime.dayNames.map((name,i) =>
			E('li',name).cls('current', day.format('Ym') == now.format('Ym') && now.getWeekDay() == i)
		))); // headers

		day.setWeekDay(0);
		while (day.format('Ym') <= start.format('Ym')) {
			const row = E('ol',
					E('li',day.getWeekOfYear()).cls('weeknb'),
					E('li',...this.drawWeek(day)).cls('events')
				);
			for (var i = 0; i < 7; i++) {
				row.append(E('li',
					E('em',day.format(day.getDate() === 1 ? 'j M' : 'j'))
				).attr('data-date', day.format('Y-m-d'))
				 .cls('today', day.format('Ymd') === now.format('Ymd'))
				 .cls('past', day.format('Ymd') < now.format('Ymd'))
				 .cls('other', day.format('Ym') !== start.format('Ym')))

				day.addDays(1);
			}
			this.el.append(row);
		}

		//this.dom.html('<div class="monthview">'+html+'</div>');

		// const //anim = this.dom!.has('.anim'),
		// 	 el = this.dom!.html('<div class="cal month active">'+html+'</div>', anim ? -1 : undefined),
		// 	curr = el.prev();

		//this.el.append(weeks);
		// if (anim && curr) { // Render new view and transist it into the old with css
		// 	//el.cls('+active');
		// 	curr.cls('-active');
		// 	// we cant use an 'animationend' event it wont fire when the animation is missing
		// 	setTimeout(function(){curr.remove(); },1375);  // could be anywere in the future after the animation
		// }
		
		//this.fire('render', start);
		//this.waiting = false;
	}

	private drawWeek(start: DateTime) {
		let end = start.clone(),
			i=0, e;
		end.addDays(7);
		let eventEls = [];
		this.slots = {0:{},1:{},2:{},3:{},4:{},5:{},6:{}};
		//debugger;
		for(var storeIt in this.recur) { 
			const r = this.recur[storeIt];
			while(r.current < end){
				eventEls.push(this.drawEvent(this.store.get(storeIt), r.current, start));
				r.next(); 
			}
		}
		for (e of this.store.items) {
			if(e.start.date().format('Yw') === start.format('Yw') && !e.recurrenceRule) {
				eventEls.push(this.drawEvent(e, new DateTime(e.start), start));
			}
		}
		return eventEls;
	}

	// onRender(dom){
	// 	this.setDate(new Date());
	// 	dom.on('click',(ev) => {
	//
	// 		const event = ev.target.up('.event');
	// 		if(event) {
	// 			const dlg = new EventDialog();
	// 			dlg.show(event).form.load(event.dataset.id);
	//
	// 		}
	// 		const day = ev.target.up('li[data-date]');
	// 		if(day) {
	// 			const dlg = new EventDialog();
	// 			//const date = Date.fromYmd(day.dataset.date);
	// 			dlg.show(event).form.create({start: day.dataset.date, end: day.dataset.date})
	//
	// 		}
	// 		ev.preventDefault();
	// 	});
	// }

	private slots: any;
	private ROWHEIGHT = 26;


	private calcRow(start: number, days: number) {
		let row = 1, end = Math.min(start+days, 7);
		while(row < 8) {
			for(let i = start; i < end; i++) {
				if(this.slots[i][row]){ // used
					break; // next row
				}
				if(i == end-1) {
					// mark used
					for(let j = start; j < end; j++) {
						this.slots[j][row] = true; 
					}
					return row;
				}
			}
			row++;
		}
		return 10;
	}

	drawEvent(e: any, eStart: DateTime, weekstart: DateTime) {
		let d = e.duration.match(/P.*(\d+)D/);
		const cal = go.Db.stores('Calendar').get(e.calendarId);
		let color = cal ? cal.color : '356772',
			start = eStart.clone(),
			days = d ? +d[1] : 1;
		let row = this.calcRow(start.getWeekDay(),days);

		let width = Math.min(7, days) * (100 / 7)- .2,
			left = Math.floor((start.getTime() - weekstart.getTime())/864e5) * (100 / 7),
			top = row * this.ROWHEIGHT,
			style = `background-color:#${color}; width: ${width}%; left:${left}%; top:${top}px;`;
		//debugger;
		return super.eventHtml(e,style);
	}
}