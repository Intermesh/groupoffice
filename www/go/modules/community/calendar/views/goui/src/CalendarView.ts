import {comp, Component} from "@goui/component/Component";
import {jmapstore} from "@goui/jmap/JmapStore.js";
import {Recurrence} from "@goui/util/Recurrence.js";
import {E} from "@goui/util/Element.js";
import {DateTime} from "@goui/util/DateTime.js";
import {win} from "@goui/component/Window.js"
import {t} from "@goui/Translate.js";
import {EventDialog} from "./EventDialog.js";
import {menu} from "@goui/component/menu/Menu.js";
import {btn} from "@goui/component/Button.js";
import {calendarStore} from "./Index.js";
import {tbar} from "@goui/component/Toolbar.js";

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
		btn({icon: 'import_export', text: t('Download ICS')})
	);

	protected selected: CalendarItem[] = []
	protected viewModel: CalendarItem[] = []

	protected store: any

	constructor() {
		super();
		this.store = jmapstore({
			entity:'CalendarEvent',
			properties: ['title', 'start','duration','calendarId','showWithoutTime','alerts','recurrenceRule','id'],
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

	save(ev: CalendarItem, onCancel) {
		const newStart = ev.start.format('Y-m-dTH:i:s'),
			newDuration = ev.start.diff(ev.end);

		if (newStart != ev.data.start || newDuration != ev.data.duration) {
			ev.data.start = newStart;
			ev.data.duration = newDuration;
			if(ev.data.id && !ev.key.includes('/')) {
				// quick save:
				this.store.entityStore.save(ev.data, ev.data.id); // await?
			} else {
				this.editItem(ev, onCancel);
			}
		}
	}

	protected editItem(ev:CalendarItem = this.current!, onCancel) {
		//if (!ev.data.id) {
			const dlg = new EventDialog();
			dlg.on('close', () => {
				// cancel ?
				onCancel();
				// did we save then show loading circle instead
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
			color = e.color || calendarStore.items.find(c => c.id == e.calendarId)?.color || '356772',
			items = [];
		if(end.date > from.date && !e.recurrenceRule) {
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
			if(r.current.date < until.date) {
				do {
					const recurrenceId = r.current.format('Y-m-d\Th:i:s');
					if(e.recurrenceOverrides?.[recurrenceId]) {
						alert('TODO!');
					}
					items.push({
						key: e.id+'/'+recurrenceId,
						recurrenceId: recurrenceId,
						start: r.current.clone(),
						end: r.current.clone().addDuration(e.duration),
						data:e,
						divs:{},
						color
					});
				} while(r.current.date < until.date && r.next())
			}
		}
		return items;
	}

	private current?: CalendarItem
	protected eventHtml(item: CalendarItem) {
		const e = item.data;
		const items = [], icons = [],
			 start = new DateTime(e.start);
		if(e.recurrenceRule) icons.push(E('i','refresh').cls('icon'));
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');

		items.push(
			E('em',...icons, e.title || '('+t('Nameless')+')'),
			E('span',  e.showWithoutTime === false ? start.format('G:i'):'')
		);
		return E('div', ...items).cls('event')
			.cls('allday',e.showWithoutTime)
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

	protected removeItem(item:CalendarItem = this.current!) {

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

	protected ROWHEIGHT = 25;

	// for full day view
	protected makestyle(e: CalendarItem, weekstart: DateTime, row?: number) {
		const day = weekstart.diffInDays(e.start),
			pos = Math.max(0,day);
		let length = e.start.diffInDays(e.end) || 1;
		if(day < 0) {
			length+= day
		}
		row = row ?? this.calcRow(pos, length);

		const width = Math.min(14, length) * (100 / Math.min(this.days,7))- .2,
			left = pos * (100 / Math.min(this.days,7)),
			top = row * this.ROWHEIGHT;
		return `color: #${e.color}; width: ${width-.5}%; left:${left}%; top:${top}px;`;
	}

	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void

	protected abstract populateViewModel(): void
}