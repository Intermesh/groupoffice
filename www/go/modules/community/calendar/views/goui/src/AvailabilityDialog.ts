import {
	t,
	E,
	btn,
	tbar,
	comp,
	h2,
	list,
	avatar,
	store,
	Window,
	Component,
	DateTime,
	ComponentEventMap, ObservableListenerOpts, WindowEventMap
} from "@intermesh/goui";
import {client, jmapds} from '@intermesh/groupoffice-core';
import {CalendarItem} from "./CalendarItem.js";
import {CalendarView} from "./CalendarView.js";

export interface AvailabilityDialogEventMap<Type> extends WindowEventMap<Type> {
	changetime: (me: Type, start: DateTime, end:DateTime) => void
}

export interface AvailabilityDialog extends Window {
	on<K extends keyof AvailabilityDialogEventMap<this>, L extends Function>(eventName: K, listener: Partial<AvailabilityDialogEventMap<this>>[K], options?: ObservableListenerOpts): L;
	fire<K extends keyof AvailabilityDialogEventMap<this>>(eventName: K, ...args: Parameters<AvailabilityDialogEventMap<any>[K]>): boolean
}

export class AvailabilityDialog extends Window {

	date!: DateTime
	dateCmp:Component
	view:Component

	event!: CalendarItem

	private principalIds!: string[]
	private evTime!:{start: DateTime, end:DateTime}
	private scheduleContainers: {[principalId:string]: HTMLElement} = {}
	private planContainer!: HTMLUListElement
	private eventEl!: HTMLElement
	private durationM = 99999;
	private busyPeriods: {start:number,end:number}[] = []

	constructor() {
		super();
		this.title = t('Availability');
		this.width = 1280;
		this.resizable = false;
		this.items.add(
			tbar({cls:'border-bottom'},
				btn({icon:'arrow_back', handler: ()=>{this.setDate(this.date.addDays(-1))}}),
				btn({icon:'arrow_forward', handler: ()=>{this.setDate(this.date.addDays(1))}}),
				this.dateCmp = h2({html:t('Today')})
			),
			this.view = comp({cls:'cal availability'})
		);
		this.renderView();

	}

	renderView() {
		this.view.el.append(E('ol',
			E('li', E('em',t('Invitees'))),
			E('li', ...Array.from({length:23}).map((_,i) => E('em',(i+1+"").padStart(2,'0'))))
		), this.planContainer = E('ul'));

		this.makeDraggable(this.planContainer);
	}

	private makeDraggable(el:HTMLElement) {
		const SNAP = 5; // minutes
		let pxPerSnap:number,
			offset:number,
		startM:number,
		endM: number;
		const mouseMove = (e: MouseEvent) => {
			 startM = Math.round((e.clientX - offset) / pxPerSnap) * SNAP;
			 endM = startM + this.durationM;
			this.eventEl.attr('style', `left: ${100 / 1440 * startM}%; width: ${100 / 1440 * (endM-startM)}%`);
		},mouseUp = (e: MouseEvent) => {
			el.un('mousemove', mouseMove);
			window.removeEventListener('mouseup', mouseUp);
			this.fire('changetime',this,
				this.date.clone().setHours(0, startM),
				this.date.clone().setHours(0, endM)
			);
		};
		el.on('mousedown', (e) => {
			if(e.button !== 0) return;

			pxPerSnap = el.offsetWidth / (1440 / SNAP);
			offset = el.getBoundingClientRect().left
			if(e.target == this.eventEl) {
				offset += e.offsetX;
			} else {
				offset += (this.eventEl.offsetWidth/2)
			}

			el.on('mousemove', mouseMove);
			mouseMove(e);
			window.addEventListener('mouseup', mouseUp);
		})
	}

	private clear() {
		this.view.el.querySelectorAll('dl').forEach(dl => dl.remove());
		this.scheduleContainers = {};
	}

	private setEvent() {
		this.clear();

		jmapds('Principal').get(this.principalIds).then(response => {
			for(const p of response.list) {

				let available = p.id == go.User.id ? 'Organizer' : 'Beschikbaar' ;
				const mAvatar = avatar({cls:"",displayName: p.name, backgroundImage: p.avatarId ? client.downloadUrl(p.avatarId) : undefined});

				this.scheduleContainers[p.id] = E('dd');
				this.view.el.append(E('dl',
					E('dt',
						mAvatar.el,
						E('h3',p.name),
						E('h4',available)
					),
					this.scheduleContainers[p.id]
				));
			}
		});
	}

	private findTime() {
		// find open spaces that are at least duration< long

		let startM = 0, endM;
		this.busyPeriods.sort((a,b)=> a.start - b.start);
		for(const current of this.busyPeriods) {
			endM = current.start;
			if(this.durationM <= endM-startM) {
				this.addFreeEl(startM,endM);
			}
			startM = current.end;
		}
		endM = 1440;
		if(this.durationM <= endM-startM){
			this.addFreeEl(startM,endM);
		}
	}

	private addFreeEl(startM:number,endM:number) {
		this.planContainer.append(E('li')
			.attr('style', `left: ${100 / 1440 * startM}%; width: ${100 / 1440 * (endM-startM)}%`));
	}

	private mergeBusyPeriod(start:number,end:number) {
		let merged = null;
		for(const c of this.busyPeriods) {
			if((start <= c.end && end >= c.start)) { //overlap
				if(merged) { // join together
					c.start = Math.min(merged.start, c.start, start); //smallest start
					c.end = Math.max(merged.end, c.end, end); // largest end
					this.busyPeriods.splice(this.busyPeriods.indexOf(merged), 1);
					return;
				} else {
					c.start = Math.min(start,c.start); //smallest start
					c.end = Math.max(end, c.end); // largest end
				}
				merged = c;
			}
 		}
		if(!merged)
			this.busyPeriods.push({start,end});
	}

	private setDate(date: DateTime) {

		for(const pid in this.scheduleContainers) {
			this.scheduleContainers[pid].innerHTML = '';
		}
		this.planContainer.innerHTML='';
		this.planContainer.append(this.eventEl = E('li').cls('event'));

		this.date = date;
		this.dateCmp.html = date.format('D d F Y');
		const start = date,
		end = date.clone().addDays(1);
		this.busyPeriods = [];

		// if today draw event
		if(start.date < this.evTime.end.date && end.date > this.evTime.start.date){
			const startM = this.evTime.start.getMinuteOfDay(),
				endM = this.evTime.end.getMinuteOfDay();
			this.durationM = endM - startM;
			this.eventEl.attr('style', `left: ${100 / 1440 * startM}%; width: ${100 / 1440 * (endM-startM)}%`);
		}

		const prom = [];
		for(const pId of this.principalIds) {
			//sthis.scheduleContainers[pId].innerHTML = '';
			prom.push(client.jmap('Principal/getAvailability', {start:start.format('Y-m-d'),end:end.format('Y-m-d'),id:pId}).then((response)=> {
				for(const busyPeriod of response.list) {
					const startM = DateTime.createFromFormat(busyPeriod.utcStart, 'Y-m-d H:i:s')!.getMinuteOfDay(),
						endM = DateTime.createFromFormat(busyPeriod.utcEnd, 'Y-m-d H:i:s')!.getMinuteOfDay();
					this.mergeBusyPeriod(startM, endM);
					this.scheduleContainers[pId].append(E('div')
						.attr('style', `left: ${100 / 1440 * startM}%; width: ${100 / 1440 * (endM-startM)}%`))
				}

			}));
		}
		Promise.all(prom).then(()=> {
			this.findTime();
		})
	}

	show(item?:CalendarItem, modified?:any){
		if(item) {
			const data = Object.assign({}, item.data, modified);
			this.evTime = {
				start: new DateTime(data.start),
				end: new DateTime(data.end)};
			this.principalIds = Object.keys(data.participants!);
			this.setDate(new DateTime(data.start));
			this.setEvent();
		}
		return super.show();
	}
}