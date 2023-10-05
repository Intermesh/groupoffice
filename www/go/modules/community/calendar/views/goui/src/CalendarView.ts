import {
	btn,
	comp,
	Component, DataSourceStore,
	datasourcestore,
	DateTime,
	E,
	menu,
	Recurrence,
	t,
	tbar,
	win
} from "@intermesh/goui";
import {EventDialog} from "./EventDialog.js";
import {calendarStore} from "./Index.js";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";

export interface CalendarEvent {
	recurrenceRule?: any
	recurrenceOverrides?: any
	links?: any
	alerts?: any
	showWithoutTime: boolean // isAllDay
	duration: string
	id?: string
	start: string
	title: string
	color?: string
	calendarId: string
}

export interface CalendarItem {
	key: string // id/recurrenceId

	recurrenceId?:string
	data: CalendarEvent
	start: DateTime
	end: DateTime
	color: string
	divs: {[week: string] :HTMLElement}
}

export abstract class CalendarView extends Component {
	
	protected day: DateTime = new DateTime()
	protected days: number = 1
	protected firstDay?: DateTime
	protected recur?: {[id:string]: Recurrence}
	protected contextMenu = menu({removeOnClose:false},
		btn({icon:'open_with', text: t('Show')}),
		btn({icon:'edit', text: t('Edit'), handler: _ => this.editItem()}),
		btn({icon:'email', text: t('E-mail participants')}),
		//'-',
		btn({icon:'delete', text: t('Delete'), handler: _ => this.removeItem() }),
		btn({icon: 'import_export', text: t('Download ICS'), handler: _ => this.downloadIcs() })
	);

	protected selected: CalendarItem[] = []
	protected viewModel: CalendarItem[] = []

	protected store: DataSourceStore<JmapDataSource>

	constructor() {
		super();
		this.store = datasourcestore({
			dataSource:jmapds('CalendarEvent'),
			//properties: ['title', 'start','duration','calendarId','showWithoutTime','alerts','recurrenceRule','id'],
			listeners: {'load': (me,records) => this.update()}
		});
		this.el.on('keydown', (e: KeyboardEvent) => {
			if(e.key == 'Delete') {
				const i = this.viewModel.indexOf(this.selected[0]);
				if(i > -1) {
					this.viewModel.splice(i,1);
				}
			}
		});
		this.on('render', () => { this.store.load() });
	}

	save(ev: CalendarItem, onCancel: Function) {
		const newStart = ev.start.format('Y-m-dTH:i:s'),
			newDuration = ev.start.diff(ev.end);

		if (newStart != (ev.recurrenceId || ev.data.start) || newDuration != ev.data.duration) {
			ev.data.start = newStart;
			ev.data.duration = newDuration;
			if(ev.data.id && !this.isRecurring(ev)) {
				// quick save:
				this.store.dataSource.update(ev.data); // await?
			} else {
				this.editItem(ev, onCancel);
			}
		}
	}

	private isRecurring(ev: CalendarItem) {
		return ev.key.includes('/');
	}

	protected editItem(ev:CalendarItem = this.current!, onCancel?: Function) {
		//if (!ev.data.id) {
		const dlg = new EventDialog();
		dlg.on('close', () => {
			// cancel ?
			onCancel && onCancel();
			// did we save then show loading circle instead
			if(!ev.key) // new
				Object.values(ev.divs).forEach(d => d.remove());

		})
		dlg.show();
		dlg.load(ev);
	}

	update = (data?: any) => {
		//this.fire('change', data);
		//this.dom.cls('-loading');
		//if(this.isRendered()) {
		this.populateViewModel();
			//this.renderView();
		//}
	}

	protected makeItems(e: CalendarEvent, from: DateTime, until: DateTime) {
		const start = new DateTime(e.start),
			end = start.clone().addDuration(e.duration),
			color = e.color || calendarStore.items.find((c:any) => c.id == e.calendarId)?.color || '356772',
			items = [];
		if(end.date > from.date && start.date < until.date && !e.recurrenceRule) {
			items.push({
				key: e.id+"",
				start,
				end,
				data:e,
				divs:{},
				color
			});
		}
		if(e.recurrenceRule) {
			const r = new Recurrence({dtstart: new Date(e.start), rule: e.recurrenceRule, ff: from.date});
			let rEnd = r.current.clone().addDuration(e.duration);
			while(r.current.date < until.date && rEnd.date > from.date) {
			//if(r.current.date < until.date) {
				//do {
					//const rEnd = r.current.clone().addDuration(e.duration);
					//if(rEnd.date > from.date) {
						const recurrenceId = r.current.format('Y-m-d\Th:i:s');
						if (e.recurrenceOverrides?.[recurrenceId]) {
							debugger; //todo
						}
						items.push({
							key: e.id + '/' + recurrenceId,
							recurrenceId: recurrenceId,
							start: r.current.clone(),
							end: rEnd,
							data: e,
							divs: {},
							color
						});
						r.next();
						rEnd = r.current.clone().addDuration(e.duration);
					//}
				//} while(r.current.date < until.date && r.next())
			}
		}
		return items;
	}

	private current?: CalendarItem
	protected eventHtml(item: CalendarItem) {
		const e = item.data;
		const icons = [],
			 start = new DateTime(e.start);
		if(e.recurrenceRule) icons.push(E('i','refresh').cls('icon'));
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');

		return E('div',
			E('em',...icons, e.title || '('+t('Nameless')+')'),
			E('span',  e.showWithoutTime === false ? start.format('G:i'):'')
		).cls('allday',e.showWithoutTime)
			.attr('data-key', item.key || '_new_')
			.attr('tabIndex', 0)
			.on('click',(ev)=> {
				// if not holdign ctrl or shift, deselect
				while(this.selected.length) {
					Object.values(this.selected.shift()!.divs).forEach(el => el.cls('-selected'));
				}
				Object.values(item.divs).forEach(d => d.cls('+selected'));
				this.selected.push(item);
				console.log(item);
				// if(!ev.target.has('.moving')) {
				// 	const dlg = new EventDialog();
				// 	dlg.show();
				// 	dlg.form.load(e.id);
				// }
			})
			.on('contextmenu', ev => {
				// todo: set id first
				this.current = item;
				this.contextMenu.showAt(ev);
				ev.preventDefault();
			}).on('dblclick', ev => {
				this.editItem(item, ()=>{});
			});
	}

	protected clear() {
		this.viewModel.forEach(ev => {
			Object.values(ev.divs).forEach(d => d.remove());
		})
		this.viewModel = [];
	}

	protected removeItem(ev:CalendarItem = this.current!) {
		if(!this.isRecurring(ev)) {
			this.store.dataSource.destroy(ev.data.id);
		} else {
			const w = win({
					title: t('Do you want to delete a recurring event?'),
					modal: true,
				},comp({
					cls:'pad',
					html: t('You will be deleting a recurring event. Do you want to delete this occurrence only or all future occurrences?')
				}),tbar({},btn({
						text: t('This event'),
						cls:'primary',
						handler: b => { this.removeOccurrence(ev.data.id, ev.recurrenceId); }
					}),btn({
						text: t('All future events'),
						handler: b => { this.removeFutureEvents(ev); }
					}),'->',btn({
						text: t('Cancel'), // save to series
						handler: b => w.close()
					})
				)
			)
			w.show();
		}
	}

	private removeOccurrence(id, recurrenceId) {
		this.store.dataSource.update({id:id, recurrenceOverrides:{[recurrenceId]:{excluded:true}}});
	}

	private removeFutureEvents(ev: CalendarItem) {
		ev.data.recurrenceRule.until = ev.recurrenceId;
		this.store.dataSource.update({id: ev.data.id as string, recurrenceRule: ev.data.recurrenceRule});
	}

	protected downloadIcs(){
		client.downloadBlobId('community/calendar/ics/'+this.current?.key, 'test.ics');
	}

	protected slots: any;
	protected calcRow(start: number, days: number) {
		let row = 0, end = Math.min(start+days, 7);
		while(row < 7) {
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

	protected ROWHEIGHT = 22;

	// for full day view
	protected makestyle(e: CalendarItem, weekstart: DateTime, row?: number): Partial<CSSStyleDeclaration> {
		const day = weekstart.diffInDays(e.start),
			pos = Math.max(0,day);
		let length = e.start.diffInDays(e.end) || 1;
		if(day < 0) {
			length+= day
		}
		row = row ?? this.calcRow(pos, length);

		const width = Math.min(14, length) * (100 / Math.min(this.days,7)),
			left = pos * (100 / Math.min(this.days,7)),
			top = row * this.ROWHEIGHT;
		return {
			width: (width-.5).toFixed(2)+'%',
			left : left.toFixed(2)+'%',
			top: top.toFixed(2)+'px',
			color: '#'+e.color
		};// `color: #${e.color}; width: ${(width-.5).toFixed(2)}%; left:${left.toFixed(2)}%; top:${top.toFixed(2)}px;`;
	}

	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void

	protected abstract populateViewModel(): void
}