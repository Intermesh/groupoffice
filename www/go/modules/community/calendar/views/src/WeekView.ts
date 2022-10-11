class WeekView extends CalendarView {

	dragGhost: any
	private px30min = 0;
	private top = 0;

	setDate(day:any) {
		if(!day) {
			day = new Date();
		}
		if(this.isRendered()) {
			this.dom.cls('reverse',(day < this.day));
		}
		this.day = new Date(+day);
		let end = new Date(+day);

		day.setDay(Date.firstWeekday); // start monday
		this.firstDay = new Date(day.toDateString());
		end.setDay(Date.firstWeekday+7); //end sunday after last day
		//this.renderView();
		//this.dom.cls('+loading');
		this.query.filter('date', {after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00')}).fetch(0,500);
	}

	onRender(dom){
		super.onRender(dom);

		dom.on('mousedown', (ev: MouseEvent) => {
			if(ev.button!== 0) return;
			let el = ev.target as HTMLElement;
			//console.log(this.dom.lastElementChild.lastElementChild.lastElementChild);
			const li = this.dom!.lastElementChild!.lastElementChild!.lastElementChild as HTMLElement;
			this.px30min = li.offsetHeight / 48;
			this.top = li.getBoundingClientRect().top;

			if(el.isA('li')) { // CREATE
				const startM = Math.floor((ev.clientY - this.top) / this.px30min)*30,
					data = {
						start: (new Date(el.dataset.day!)).changeTime(0,startM).toJmap(),
						duration: 'PT30M'
					},
					div = el.html(this.drawEvent(data), -1);
				this.dragGhost = data;
				this.moveEvent(div,data,startM);
			} else {
				el = el.up('.event');
				if(el) { // MOVE
					const data = this.store.get(el.dataset.id!) || this.dragGhost;
					let anchor;
					if(ev.offsetY <= 2) {
						anchor = this.m(data.start.date().addDuration(data.duration)); // endM
					} else if(ev.offsetY >= el.offsetHeight - 2) {
						anchor = this.m(data.start.date()); //startM
						console.log(anchor);
					}
					// no acnhor = use mouse as offset
					this.moveEvent(el,data,anchor);
				}
			}
			
		});
	}

	private moveEvent(el:HTMLElement, data, anchor) {
		el.cls('+moving');
		let r,last:number;
		Draggable.start((e: MouseEvent) => {
			const time = Math.floor((e.clientY - this.top) / this.px30min)*30;
			if(time !== last) {
				last = time;
				r = this.onDrag(el, data, time, anchor);
			}
			return r;
		}, (e: MouseEvent, start:Date, end:Date) => {
			// update data
			setTimeout(()=>{ el.cls('-moving'); }); // prevent dialog from opening 
			if(!data.id) {
				data.start = start.toJmap();
				data.duration = start.diff(end);
				const dlg = new EventDialog();
				dlg.show().form.create(data);
			}
			console.log(start, data.duration);
			//event.remove(); this.dragGhost = null;
		});
	}

	private m = (date:Date) => date.getHours()*60+date.getMinutes();

	private onDrag(event:HTMLElement, data, minute:number, anchor?:number) {
		let start = data.start.date();	
		if(!anchor) { // move
			start.changeTime(0,minute);
		}
		let end = start.clone().addDuration(data.duration);
		if(anchor) {
			if(minute > anchor) { // drag below
				start.changeTime(0,anchor)
				end.changeTime(0,minute)
			} else { // drag above anchor
				start.changeTime(0,minute);
				end.changeTime(0,anchor)
			}
		} 

		const startM = this.m(start),
			endM = Math.min(1450,this.m(end));
		event.style.top = (100 / 1440 * startM)+'%';
		event.style.height = (100 / 1440 * (endM - startM))+'%';
		event.lastElementChild!.textContent = start.to('H:i')+' - '+end.to('H:i');
		return [start,end];
	}

	private changeTime(event, d , minutes: number, type: 'start'|'end'|'move') {
		// toggle end/start when end < start
		let start = d.start.date();
		if(type == 'move') {
			start.changeTime(0,minutes);
		}
		let end = start.clone().addDuration(d.duration);
		switch(type) {
			case 'start': start.changeTime(0,minutes); break; // increase duration
			case 'end': end.changeTime(0,minutes); break;
		}

		console.log(start, d.start,type,minutes);
		let startM = start.getHours()*60+start.getMinutes(),
			endM = end.to('Ymd') > start.to('Ymd') ? 1450 : end.getHours()*60+end.getMinutes();
		event.style.top = (100 / 1440 * startM)+'%';
		event.style.height = (100 / 1440 * (endM - startM))+'%';
		event.lastElementChild.textContent = start.to('H:i')+' - '+end.to('H:i');
	}

	renderView() {
		let now = new Date(),it = 1, hour, wd, today = -1, recur = {}, e, r,
		day = new Date(+this.day);
		day.setDay(Date.firstWeekday);

		let heads = '', allday = '', days = '', hours = '', nowbar = '';
		for (hour = 1; hour < 24; hour++) {
			hours += `<em>${hour+':00'}</em>`;
		}
		for (var i = 0; i < 7; i++) {
			let events: string[] = [], 
				evs: any[] = [], 
				alldays: string[] = [];
			for(var storeIt in this.recur) { 
				const rec = this.recur[storeIt], ev = this.store.get(storeIt);
				while(rec.current.to('Ymd') == day.to('Ymd')){ 
					if(ev.isAllDay) { alldays.push(this.drawEvent(ev)); }
					else { evs.push(ev); }
					rec.next(); 
				}
			}
			while (e = this.store.get(this.query.ids[it])) {
				if(!e.recurrenceRule && e.start.date().to('Ymd') == day.to('Ymd')) {
					if(e.isAllDay) { alldays.push(this.drawEvent(e)); }
					else { evs.push(e); }
				}
				it++;
			}
			const ov = this.calculateOverlap(evs);
			for(let ev of evs) {
				events.push(this.drawEvent(ev, ov[ev.id]));
			}
			const cls = day.to('Ymd') < now.to('Ymd') ? 'past' : (day.to('Ymd') === now.to('Ymd') ? 'today' : '');

			heads += `<li${cls ? ' class="'+cls+'"' : ''}>${day.getDayName()}<em>${day.getDate()}</em></li>`;
			allday += `<li>${alldays.join('')}</li>`;
			days += `<li data-day="${day.to('Y-m-d')}">${events.join('')}</li>`;

			if(day.to('Ymd') === now.to('Ymd')) {
				const top = 24*7 / (60*24) * (now.getHours()*60 + now.getMinutes()), // 1296 = TOTAL HEIGHT of DAY
					left = 100 / 7 * (now.getDay()==0 ? 6 : (now.getDay() - Date.firstWeekday));
				nowbar = `<div class="now" style="top:${top}vh;"><hr><b style="left: calc(${left}%);"></b><span>${now.to('H:i')}</span></div>`;
			}
			day.add(1,'d'); it=0; 
		}
		

		this.dom!.html(`<div class="cal week active"><ul><li>${this.day.getWeek()}</li>${heads}</ul><ul><li></li>${allday}</ul><ol><li>${nowbar}<em></em>${hours}</li>${days}</ol></div>`);
		const ol = this.dom!.child(0).child(2);
		ol.scrollTop = ol.scrollHeight / 100 * 25; // = scroll 6hours down (1/4 of day)
	}
   // event, overlap
	protected drawEvent(e, o?) {
		const cal = $.db.stores.Calendar.get(e.calendarId);
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