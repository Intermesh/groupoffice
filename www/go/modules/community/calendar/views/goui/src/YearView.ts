import {CalendarView, CalendarEvent} from "./CalendarView.js";
import {EventDialog} from "./EventDialog";
import {DateTime} from "@goui/util/DateTime.js";
import {datepicker} from "@goui/component/picker/DatePicker";
import {E} from "@goui/util/Element.js";
import {calendarStore} from "./Index.js";

export class YearView extends CalendarView {
	constructor() {
		super();
		this.cls = 'yearview';
	}

	setDate(day:any) {
		if(!day) {
			day = new DateTime();
		}
		// if(this.isRendered()) {
		// 	this.el.cls('reverse',(day < this.day));
		// }
		day.setDate(1).setMonth(1); // 1st jan
		this.day = day.clone();
		let end = day.clone();
		end.addYears(1);
		this.renderView();
		//this.dom.cls('+loading');
		//this.store.filter('date', {after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00')}).fetch(0,500);
	}

	// renderView() {
	// 	let day = new DateTime(this.day ? this.day.getYear() : (new DateTime()).getFullYear(),0,1);
	// 	this.items.clear();
	// 	for(var m = 0; m < 12; m++) {
	// 		this.items.add((datepicker()).setDate(day));
	// 	}
	// 	this.items.render();
	// }

	renderView() {
		const el = E('div').cls('yearview active');
		let html = ``;
		 var it=0, e,
			now = new DateTime(),
			day = this.day.clone().setDate(1).setMonth(1);
		for(var m = 0; m < 12; m++) {
		 	var firstDay = day.getWeekDay(),
				totalDays = day.getDaysInMonth();

			html += `<div>
				<table>
					<caption class="${day.format('mY') == now.format('mY') ?'current':''}" data-month="${m}">
						${DateTime.monthNames[m]}
					</caption>
					<tr>
						<td>&nbsp;</td>`;
						for(var i=0;i < 7;i++) {
							html += `<th>${DateTime.dayNames[i][0]}</th>`;
						}
					html += `</tr>
					<tr>
						<td class="weeknb" data-week="${day.getWeekOfYear()}">${day.getWeekOfYear()}</td>`;
						for(var j = 0; j < firstDay; j++) {
							html += `<td>&nbsp;</td>`;
						}
						for(var i = firstDay; i < totalDays+firstDay; i++) {
						if((i) % 7 === 0 && i !== 0) {
						html += `</tr><tr>
						<td class="weeknb" data-week="${day.getWeekOfYear()}">${day.getWeekOfYear()}</td>`;
						}
						var cls=[];
						if (day.format('Ymd') === now.format('Ymd')) cls.push('today');
						if (day.format('Ymd') < now.format('Ymd')) cls.push('past');

						html += `<td class="${cls.join(' ')}">
							<span>${day.getDate()}</span>
							<div class="events">`;
							// for(var storeIt in this.recur) {
							// 	if(this.recur[storeIt].current.format('Ymd') == day.format('Ymd')){
							// 		const cal = $.db.stores.Calendar.get(e.calendarId);
							// 		e = this.store.get(this.query.ids[storeIt]);
							// 		html += `<p title="${e.title+' - '+e.start.date().format('H:i')}" style="background-color:#${cal ? cal.color : '356772'}"></p>`;
							// 		this.recur[storeIt].next();
							// 	}
							// }
							for (const e of this.store.items) {
								if(new DateTime(e.start).format('Ymd') != day.format('Ymd')) break;
								const cal = calendarStore.items.find(c => c.id == e.calendarId);
								html += `<p title="${e.title+' - '+(new DateTime(e.start)).format('H:i')}" style="background-color:#${cal ? cal.color : '356772'}"></p>`;
								it++;
							}
							html += `</div>
						</td>`;
						 day.addDays(1);
						}
						html += `</tr>
				</table>
			</div>`;
		}

		this.el.innerHTML = (html+'');
	}
}