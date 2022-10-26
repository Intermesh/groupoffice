import {CalendarView, CalendarEvent} from "./CalendarView.js";
import {EventDialog} from "./EventDialog";
import {DateTime} from "@goui/util/DateTime.js";
import {E} from "@goui/util/Element.js";

export class WeekView extends CalendarView {

	dragGhost: any
	private px30min = 0;
	private top = 0;

	setDate(day: DateTime) {
		if(!day) {
			day = new DateTime();
		}
		this.el.cls('reverse',(day < this.day));

		this.day = day.clone();
		let end = day.clone();

		day.setWeekDay(0); // start monday
		this.firstDay = day.clone();
		end.setWeekDay(7); //end sunday after last day
		//this.renderView();
		//this.store.filter('date', {after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00')}).fetch(0,500);
	}

	// onRender(dom){
	// 	super.onRender(dom);
	//
	// 	dom.on('mousedown', (ev: MouseEvent) => {
	// 		if(ev.button!== 0) return;
	// 		let el = ev.target as HTMLElement;
	// 		//console.log(this.dom.lastElementChild.lastElementChild.lastElementChild);
	// 		const li = this.el.lastElementChild!.lastElementChild!.lastElementChild as HTMLElement;
	// 		this.px30min = li.offsetHeight / 48;
	// 		this.top = li.getBoundingClientRect().top;
	//
	// 		if(el.isA('li')) { // CREATE
	// 			const startM = Math.floor((ev.clientY - this.top) / this.px30min)*30,
	// 				data = {
	// 					start: (new Date(el.dataset.day!)).setHours(0,startM).toJmap(),
	// 					duration: 'PT30M'
	// 				} as CalendarEvent;
	// 			let div;
	// 			el.append(div = this.drawEvent(data));
	//
	// 			this.dragGhost = data;
	// 			this.moveEvent(div,data,startM);
	// 		} else {
	// 			el = el.up('.event');
	// 			if(el) { // MOVE
	// 				const data = this.store.get(el.dataset.id!) || this.dragGhost;
	// 				let anchor;
	// 				if(ev.offsetY <= 2) {
	// 					anchor = this.m(data.start.date().addDuration(data.duration)); // endM
	// 				} else if(ev.offsetY >= el.offsetHeight - 2) {
	// 					anchor = this.m(data.start.date()); //startM
	// 					console.log(anchor);
	// 				}
	// 				// no acnhor = use mouse as offset
	// 				this.moveEvent(el,data,anchor);
	// 			}
	// 		}
	//
	// 	});
	// }

	static dragStart(move: (e:Event) => any[] ,drop: (e:Event, ...data: any[]) => void) {
		let data: any[] = [];
		const moving = (e: MouseEvent) => {
			e.preventDefault();
			data = move(e);
		}, up = (e: MouseEvent) => {
			window.removeEventListener('mousemove', moving);
			window.removeEventListener('mouseup', up);
			drop(e, ...data);
		}
		window.addEventListener('mousemove',moving);
		window.addEventListener('mouseup',up)
	}

	// private moveEvent(el:HTMLElement, data, anchor) {
	// 	el.cls('+moving');
	// 	let r: [DateTime, DateTime],
	// 		last:number;
	// 	WeekView.dragStart((e: MouseEvent) => {
	// 		const time = Math.floor((e.clientY - this.top) / this.px30min)*30;
	// 		if(time !== last) {
	// 			last = time;
	// 			r = this.onDrag(el, data, time, anchor);
	// 		}
	// 		return r;
	// 	}, (e: MouseEvent, start:DateTime, end:DateTime) => {
	// 		// update data
	// 		setTimeout(()=>{ el.cls('-moving'); });
	// 		if(!data.id) {
	// 			data.start = start.format('c');
	// 			data.duration = start.diff(end);
	// 			const dlg = new EventDialog();
	// 			dlg.show();
	// 			dlg.form.setValues(data);
	// 		}
	// 		console.log(start, data.duration);
	// 		//event.remove(); this.dragGhost = null;
	// 	});
	// }

	private m(date: DateTime){ return date.getHours()*60+date.getMinutes(); }

	private onDrag(event:HTMLElement, data:CalendarEvent, minute:number, anchor?:number) {
		let start = new DateTime(data.start);
		if(!anchor) { // move
			start.setHours(0,minute);
		}
		let end = start.clone().addDuration(data.duration);
		if(anchor) {
			if(minute > anchor) { // drag below
				start.setHours(0,anchor)
				end.setHours(0,minute)
			} else { // drag above anchor
				start.setHours(0,minute);
				end.setHours(0,anchor)
			}
		} 

		const startM = this.m(start),
			endM = Math.min(1450,this.m(end));
		event.style.top = (100 / 1440 * startM)+'%';
		event.style.height = (100 / 1440 * (endM - startM))+'%';
		event.lastElementChild!.textContent = start.format('H:i')+' - '+end.format('H:i');
		return [start,end];
	}

	private changeTime(event: HTMLElement, d: CalendarEvent, minutes: number, type: 'start'|'end'|'move') {
		// toggle end/start when end < start
		let start = new DateTime(d.start);
		if(type == 'move') {
			start.setHours(0,minutes);
		}
		let end = start.clone().addDuration(d.duration);
		switch(type) {
			case 'start': start.setHours(0,minutes); break; // increase duration
			case 'end': end.setHours(0,minutes); break;
		}

		console.log(start, d.start,type,minutes);
		let startM = start.getHours()*60+start.getMinutes(),
			endM = end.format('Ymd') > start.format('Ymd') ? 1450 : end.getHours()*60+end.getMinutes();
		event.style.top = (100 / 1440 * startM)+'%';
		event.style.height = (100 / 1440 * (endM - startM))+'%';
		event.lastElementChild!.textContent = start.format('H:i')+' - '+end.format('H:i');
	}

	renderView() {
		let now = new DateTime(),
			it = 1, hour, e,
			day = this.day.clone();
		day.setWeekDay(0);

		let heads = [], allday = [], days = [], hours = [];
		for (hour = 1; hour < 24; hour++) {
			hours.push(E('em', hour+':00'));
		}
		for (var i = 0; i < 7; i++) {
			let events: HTMLElement[] = [],
				evs: any[] = [], 
				alldays: HTMLElement[] = [];
			for(var storeIt in this.recur) { 
				const rec = this.recur[storeIt], ev = this.store.get(storeIt);
				while(rec.current.format('Ymd') == day.format('Ymd')){
					if(ev.isAllDay) { alldays.push(this.drawEvent(ev)); }
					else { evs.push(ev); }
					rec.next(); 
				}
			}
			for (e of this.store.items) {
				if(!e.recurrenceRule && (new DateTime(e.start)).format('Ymd') == day.format('Ymd')) {
					if(e.isAllDay) { alldays.push(this.drawEvent(e)); }
					else { evs.push(e); }
				}
			}
			const ov = this.calculateOverlap(evs);
			for(let ev of evs) {
				events.push(this.drawEvent(ev, ov[ev.id]));
			}
			const cls = day.format('Ymd') < now.format('Ymd') ? 'past' : (day.format('Ymd') === now.format('Ymd') ? 'today' : '');

			heads.push(E('li',day.format('l'),E('em',day.getDate())).cls(cls));
			allday.push(E('li', ...alldays));
			days.push(E('li', ...events).attr('data-day', day.format('Y-m-d')));

			day.addDays(1); it=0;
		}
		const top = 24*7 / (60*24) * (now.getHours()*60 + now.getMinutes()), // 1296 = TOTAL HEIGHT of DAY
			left = 100 / 7 * now.getWeekDay();
		let nowbar = E('div', E('hr'), E('b').attr('style', `left: calc(${left}%);`),E('span', now.format('H:i'))).cls('now').attr('style', `top:${top}vh;`)

		let ol;
		this.el.cls('cal week active')
		this.el.style.height = '100%';
		this.el.append(
			E('ul',E('li',this.day.getWeekOfYear()), ...heads),
			E('ul',E('li'), ...allday),
			ol = E('ol',E('li', nowbar, E('em'), ...hours), ...days)
		);

		//this.el.innerHTML = (`<div class="cal week active"><ul><li>${this.day.getWeekOfYear()}</li>${heads}</ul><ul><li></li>${allday}</ul><ol><li>${nowbar}<em></em>${hours}</li>${days}</ol></div>`);
		//const ol = this.el.firstElementChild!.children[2];
		ol.scrollTop = ol.scrollHeight / 100 * 25; // = scroll 6hours down (1/4 of day)
	}
   // event, overlap
	protected drawEvent(e: CalendarEvent, o?: any) { // o = calculated overlap
		const cal = go.Db.store('Calendar').get(e.calendarId);
		let color = cal ? cal.color : '356772', 
			style = `background-color:#${color};`;
		if(o) {
			let minutes = o.end - o.start,
			top = 100 / 1440 * o.start, // 1440 minutes in day
			left = o.col * (100 / o.max),
			width = (100 / o.max) * o.span - .2,
			height = 100 / 1440 * minutes;
			style += ` height: calc(${height}% - 1px); top: ${top}%; left: ${left}%; width: ${width}%;`;
		}

		return super.eventHtml(e,style);
	}

}