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

export abstract class CalendarView extends Component {
	
	protected day: DateTime = new DateTime()
	protected days: number = 1
	protected firstDay?: DateTime
	protected recur?: {[id:string]: Recurrence}
	protected contextMenu = menu({removeOnClose:false, isDropdown: true},
		btn({icon:'open_with', text: t('Show')}),
		btn({icon:'edit', text: t('Edit'), handler: _ => this.current!.edit()}),
		btn({icon:'email', text: t('E-mail participants')}),
		//'-',
		btn({icon:'delete', text: t('Delete'), handler: _ => this.current!.remove() }),
		btn({icon: 'import_export', text: t('Download ICS'), handler: _ => this.current!.downloadIcs() })
	);

	protected selected: CalendarItem[] = []
	protected viewModel: CalendarItem[] = []

	protected store: DataSourceStore<JmapDataSource<CalendarEvent>>

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

	update = (data?: any) => {
		//this.fire('change', data);
		//this.dom.cls('-loading');
		//if(this.isRendered()) {
		this.populateViewModel();
			//this.renderView();
		//}
	}

	private current?: CalendarItem
	protected eventHtml(item: CalendarItem) {
		const e = item.data;
		const icons = []
		if(e.recurrenceRule) icons.push(E('i','refresh').cls('icon'));
		if(e.links) icons.push('attachment');
		if(e.alerts) icons.push('notifications');

		return E('div',
			E('em',...icons, item.title || '('+t('Nameless')+')'),
			E('span',  e.showWithoutTime === false ? item.start.format('G:i'):'')
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
				item.edit();
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