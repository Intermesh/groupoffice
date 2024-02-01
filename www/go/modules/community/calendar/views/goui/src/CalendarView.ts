import {
	btn,
	comp,
	Component, DataSourceStore,
	datasourcestore,
	DateTime,
	E,
	menu,
	Recurrence,
	t
} from "@intermesh/goui";
import {JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {MonthView} from "./MonthView.js";

export abstract class CalendarView extends Component {

	static selectedCalendarId: string

	protected day: DateTime = new DateTime()
	protected days: number = 1
	protected firstDay?: DateTime
	protected recur?: {[id:string]: Recurrence}
	protected contextMenu = menu({removeOnClose:false, isDropdown: true},
		//btn({icon:'open_with', text: t('Show'), handler:_ =>alert(this.current!.data.id)}),
		btn({icon:'edit', text: t('Edit'), handler: _ => this.current!.open()}),
		btn({icon:'email', text: t('E-mail participants'), handler: _ => {
				if (this.current!.data.participants){
					go.showComposer({to: Object.values(this.current!.data.participants).map((p:any) => p.email)});
				}
			}
		}),
		//'-',
		btn({icon:'delete', text: t('Delete'), handler: _ => this.current!.remove() }),
		btn({icon: 'import_export', text: t('Download ICS'), handler: _ => this.current!.downloadIcs() })
	);

	protected selected: CalendarItem[] = []
	protected viewModel: CalendarItem[] = []

	protected store: DataSourceStore<JmapDataSource<CalendarEvent>>

	constructor(store: DataSourceStore<JmapDataSource<CalendarEvent>>) {
		super();
		this.store = store
	}

	update = (data?: any) => {
		if(this.rendered) {
			this.renderView();
			this.populateViewModel();
		}
	}

	private current?: CalendarItem
	protected eventHtml(item: CalendarItem) {
		const e = item.data;
		const icons = []
		if(!e.showWithoutTime && this instanceof MonthView) icons.push('fiber_manual_record') // the dot
		if(e.recurrenceRule) icons.push('refresh');
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');
		if(!!e.participants) icons.push('group');

		let time = [];
		if(!e.showWithoutTime) {
			time.push(item.start.format('G:i'));
			if(item.dayLength > 1) {
				time.push(item.end.format(' - G:i'));
			}
		}

		return E('div',
			...icons.map(i=>E('i',i).cls('icon')),
			E('em', item.title || '('+t('Nameless')+')'),
			E('span',  time[0]||'',time[1]||'')
		).cls('allday',e.showWithoutTime)
			.cls('declined', item.currentParticipant?.participationStatus === 'declined')
			.cls('multiday', !e.showWithoutTime && item.dayLength > 1)
			.attr('data-key', item.key || '_new_')
			.attr('tabIndex', 0)
			.on('click',(ev)=> {
				// if not holdign ctrl or shift, deselect
				while(this.selected.length) {
					Object.values(this.selected.shift()!.divs).forEach(el => el.cls('-selected'));
				}
				Object.values(item.divs).forEach(d => d.cls('+selected'));
				this.selected.push(item);
				//console.log(item);
				// if(!ev.target.has('.moving')) {
				// 	const dlg = new EventDialog();
				// 	dlg.show();
				// 	dlg.form.load(e.id);
				// }
			})
			//.on('mousedown', ev => ev.stopPropagation()) /* when enabled cant drag event in monthview */
			.on('contextmenu', ev => {
				// todo: set id first
				this.current = item;
				this.contextMenu.showAt(ev);
				ev.preventDefault();
			}).on('dblclick', ev => {
				item.open();
			});
	}

	protected clear() {
		this.viewModel.forEach(ev => {
			Object.values(ev.divs).forEach(d => d.remove());
		})
		this.viewModel = [];
	}

	protected slots: any;
	protected calcRow(start: number, days: number) {
		let row = 0, end = Math.min(start+days, 7);
		while(row < 10) {
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

	protected ROWHEIGHT = 31;

	// for full day view
	protected makestyle(e: CalendarItem, weekstart: DateTime, row?: number): Partial<CSSStyleDeclaration> {
		const day = weekstart.diff(e.start).getTotalDays()!,
			pos = Math.max(0,day);
		let length = e.dayLength;
		//console.log(length, e.title,e.start.format('d-m-Y H:i:s'), e.end.format('d-m-Y H:i:s'));
		// if(day < 0) {
		// 	length+= day
		// }
		row = row ?? this.calcRow(pos, length);

		const width = Math.min(14, length) * (100 / Math.min(this.days,7)),
			left = pos * (100 / Math.min(this.days,7)),
			top = row * this.ROWHEIGHT;
		return {
			width: (width-.3).toFixed(2)+'%',
			left : left.toFixed(2)+'%',
			top: (top/10).toFixed(2)+'rem',
			color: '#'+e.color
		};// `color: #${e.color}; width: ${(width-.5).toFixed(2)}%; left:${left.toFixed(2)}%; top:${top.toFixed(2)}px;`;
	}

	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void

	protected abstract populateViewModel(): void
}