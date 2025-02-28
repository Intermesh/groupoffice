import {
	btn,
	Component,
	DateTime,
	E,
	menu
} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {client,Recurrence} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

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

	protected adapter: CalendarAdapter

	constructor(adapter: CalendarAdapter) {
		super();
		CalendarView.selectedCalendarId = client.user.calendarPreferences?.defaultCalendarId; // default
		this.adapter = adapter
	}

	update = (_data?: any) => {
		if(this.rendered) {
			//this.renderView();
			this.populateViewModel();
		}
	}

	private current?: CalendarItem
	protected eventHtml(item: CalendarItem, div?:HTMLElement) {
		const e = item.data;

		if(!div) { // default
			const time = E('span');
			if(!e.showWithoutTime) {
				time.append(item.start.format('G:i'));
				if(item.dayLength > 1) {
					time.append(item.end.format(' - G:i'));
				}
			}
			div = E('div',
				E('em', item.title || '('+t('Nameless')+')'),
				...item.icons,
				time
			)
		}
		if(item.key) {
			div.dataset.key = item.key;
		}
		return div.cls('allday',e.showWithoutTime)
			.cls('declined', item.isDeclined || item.isCancelled)
			.cls('undecided', item.needsAction)
			.cls('multiday', !e.showWithoutTime && item.dayLength > 1)
			.attr('tabIndex', 0)
			.on('click',(_ev)=> {
				this.focus(); // for catching keydown event
				// if not holding ctrl or shift, deselect
				while(this.selected.length) {
					Object.values(this.selected.shift()!.divs).forEach(el => el.cls('-selected'));
				}
				Object.values(item.divs).forEach(d => d.cls('+selected'));
				this.selected.push(item);
			})
			//.on('mousedown', ev => ev.stopPropagation()) /* when enabled cant drag event in monthview */
			.on('contextmenu', ev => {
				// todo: set id first
				if(!item.key) return;
				this.current = item;
				this.contextMenu.showAt(ev);
				ev.preventDefault();
			}).on('dblclick', _ev => {
				if(!item.key) return;
				item.open();
			});
	}

	protected clear() {
		this.viewModel.forEach(ev => {
			Object.values(ev.divs).forEach(d => d.remove());
		})
		this.viewModel = [];
	}

	protected slots!: any[];
	protected calcRow(start: number, days: number) {
		let row = 0,
			end = Math.min(start+days, this.slots.length);
		while(row < 40) {
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
		return 40;
	}

	protected ROWHEIGHT = 2.6;

	// for full day view
	protected makestyle(e: CalendarItem, weekstart: DateTime, row?: number): Partial<CSSStyleDeclaration> {
		const dayDiff = weekstart.diff(e.start),
			days = dayDiff.getTotalDays()!,
			pos = dayDiff.invert ? 0 : days,
			dwidth = e.dayLength - (dayDiff.invert ? days : 0);

		row = row ?? this.calcRow(pos,dwidth);

		const width = Math.min(21, dwidth) * (100 / this.slots.length),
			left = pos * (100 / this.slots.length),
			top = row * this.ROWHEIGHT;
		return {
			width: (width-.6).toFixed(2)+'%',
			left : (left+(dayDiff.invert?0:.3)).toFixed(2)+'%',
			top: top.toFixed(2)+'rem',
			color: '#'+e.color
		};
	}

	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void

	protected abstract populateViewModel(): void
}