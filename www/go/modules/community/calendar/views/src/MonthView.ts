class MonthView extends CalendarView {

	private stale = true

	setDate(day:any) {
		if(!day) {
			let now = new Date();
			day = new Date(now.getFullYear(), now.getMonth(), 1);
		}
		this.dom.cls('reverse',(day < this.day));
		this.day = new Date(+day);
		let end = new Date(+day);
		day.setDate(1);
		day.setDay(Date.firstWeekday); // start monday
		this.firstDay = new Date(day.toDateString());
		end.setMonth(end.getMonth()+1);
		end.setDate(0);
		end.setDay(6); //end sunday after last day
		end.add(1,'d');
		//this.renderView();
		//this.dom.cls('+loading');
		this.store.filter('date', {after: day.to('Y-m-dT00:00:00'), before: end.to('Y-m-dT00:00:00')}).fetch(0,500);
	}

	renderView() {
		let it = 0;
		if (!this.stale) return;
		let now = new Date(),
			 day = new Date(this.day.toDateString()); // toDateString removes time
		day.setDate(1);
		let start = new Date(+day), e,
			html = `<ul>`;
		for (var i = 0; i < 7; i++) { // header
			let d = (i + Date.firstWeekday) % 7;
			html += `<li class="${(day.to('Ym') == now.to('Ym') && now.getDay() == d)?'current':''}">${Date.days[d]}</li>`;
		}
		day.setDay(Date.firstWeekday); // start monday
		html += `</ul>`;
		while (day.to('Ym') <= start.to('Ym')) {
			html += `<ol>
				<li class="weeknb">${day.getWeek()}</li>
				<li class="events">${this.drawWeek(day)}</li>`;
			for (var i = 0; i < 7; i++) {
				var cls:string[] =[];
				if (day.to('Ymd') === now.to('Ymd')) cls.push('today');
				if (day.to('Ymd') < now.to('Ymd')) cls.push('past');
				if (day.to('Ym') !== start.to('Ym')) cls.push('other');
				html += `<li class="${cls.join(' ')}" data-date="${day.to('Y-m-d')}"><em>${day.getDate()}</em></li>`; // day block
				
				day.add(1,'d');
			}
			html +=`</ol>`;
		}

		//this.dom.html('<div class="monthview">'+html+'</div>');

		// const //anim = this.dom!.has('.anim'),
		// 	 el = this.dom!.html('<div class="cal month active">'+html+'</div>', anim ? -1 : undefined),
		// 	curr = el.prev();
		this.dom.style.height = '100%';
		this.dom.classList.add('cal','month','active');
		this.dom!.innerHTML = html;
		// if (anim && curr) { // Render new view and transist it into the old with css
		// 	//el.cls('+active');
		// 	curr.cls('-active');
		// 	// we cant use an 'animationend' event it wont fire when the animation is missing
		// 	setTimeout(function(){curr.remove(); },1375);  // could be anywere in the future after the animation
		// }
		
		//this.fire('render', start);
		//this.waiting = false;
	}

	private drawWeek(start: Date) {
		let end = new Date(+start), i=0, e;
		end.add(7,'d');
		let html = '';
		this.slots = {0:{},1:{},2:{},3:{},4:{},5:{},6:{}};
		//debugger;
		for(var storeIt in this.recur) { 
			const r = this.recur[storeIt];
			while(r.current < end){ 
				html += this.drawEvent(this.store.get(storeIt), r.current, start);
				r.next(); 
			}
		}
		for (e of this.store.data.items) {
			if(e.start.date().to('Yw') === start.to('Yw') && !e.recurrenceRule) {
				html += this.drawEvent(e, new Date(e.start), start);
			}
		}
		return html;
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

	private slots;
	private ROWHEIGHT = 26;


	private calcRow(start, days) {
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

	drawEvent(e, eStart, weekstart) {
		let d = e.duration.match(/P.*(\d+)D/);
		const cal = $.db.stores.Calendar.get(e.calendarId);
		let color = cal ? cal.color : '356772',
			start = eStart.clone(),
			days = d ? +d[1] : 1;
		let row = this.calcRow(start.getWeekDay(),days);

		let width = Math.min(7, days) * (100 / 7)- .2,
			left = Math.floor((start - weekstart)/864e5) * (100 / 7),
			top = row * this.ROWHEIGHT,
			style = `background-color:#${color}; width: ${width}%; left:${left}%; top:${top}px;`;
		//debugger;
		return super.eventHtml(e,style);
	}
}