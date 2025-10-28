import {
	btn,
	Component,
	DateTime,
	E,
	tooltip,
	menu, Format, hr, ComponentEventMap, radio, Radiofield
} from "@intermesh/goui";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {client,Recurrence} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

export abstract class CalendarView<EventMap extends ComponentEventMap = ComponentEventMap> extends Component<EventMap> {

	static selectedCalendarId: string

	protected currentCreation?: CalendarItem
	protected day: DateTime = new DateTime()
	protected days: number = 1
	protected firstDay?: DateTime
	protected recur?: {[id:string]: Recurrence}
	protected contextMenu = menu({
			removeOnClose:false,
			isDropdown: true,
			listeners: {
				show:( {target}) => {

					const mayWrite = this.current!.mayChange;
					target.findChild("delete")!.disabled = !mayWrite;
					target.findChild("edit")!.hidden = !mayWrite;

					const participationStatus = target.findChild("participationStatus") as Radiofield;
					participationStatus.hidden = this.current!.isOwner ?? !this.current!.cal.myRights?.mayWriteAll;

					participationStatus.value = this.current?.calendarPrincipal?.participationStatus;
				}
			}
		},
		btn({itemId: "edit", icon:'edit', text: t('Edit','core','core'), handler: _ => this.current!.open()}),
		btn({itemId: "info", icon:'info', text: t('Info','core','core'), handler: _ => this.current!.info()}),
		hr(),

		radio({
			itemId: "participationStatus",
				type: "list",
				flex: 1,
				name: "participationStatus",
				options: [
					{icon:'check', text: t('Accept'), value: "accepted"},
					{icon:'question_mark', text: t('Maybe'), value: "tentative"},
					{icon:'remove_circle', text: t('Decline'), value: "declined"},
				],
				listeners: {
					change: ev => {
						ev.target.parent!.hide();
						this.current!.updateParticipation(ev.newValue, () => {
							ev.target.value = this.current?.calendarPrincipal?.participationStatus;
						});
					}
				}
			}
		),

		hr(),

		btn({itemId: "delete", icon:'delete', text: t('Delete','core'), handler: _ => this.current!.remove() }),
		btn({icon:'content_cut', text: t('Cut','core'), handler: _ => this.current!.cut() }),
		btn({icon:'content_copy', text: t('Copy','core'), handler: _ => this.current!.copy() }),
		hr(),

		btn({icon:'email', text: t('E-mail participants'), handler: _ => {
				if (this.current!.data.participants){
					go.showComposer({to: Object.values(this.current!.data.participants).map((p:any) => p.email)});
				}
			}
		}),
		//'-',

		btn({icon: 'import_export', text: t('Download ICS'), handler: _ => this.current!.downloadIcs() })
	);

	protected contextMenuEmpty = menu({removeOnClose:false, isDropdown: true, listeners: {
		beforeshow: ({target}) => {
			const btn = target.items.find(item => item.itemId === 'paste');
			if(btn) {
				btn.disabled = !CalendarItem.clipboard;
				btn.text = CalendarItem.clipboard ? t('Paste ','core') + ' '+CalendarItem.clipboard.title : t('Paste ','core');
			}
		}}},
		btn({icon:'add', text: t('Appointment'), handler: _ => {
			const date = this.contextMenuEmpty.dataSet.date;
			let start;
			if (date.length > 10) {
				start = new DateTime(date);
			} else {
				const [y, m, d] = date.split('-').map(Number);
				start = new DateTime(); // time = now
				start.setYear(y).setMonth(m).setDate(d);
			}
			(new CalendarItem({key:'',data:{
				start:start.format('Y-m-d\TH:00:00.000'),
				title: t('New event'),
				showWithoutTime: client.user.calendarPreferences?.defaultDuration == null,
				duration: client.user.calendarPreferences?.defaultDuration ?? "P1D",
				calendarId: CalendarView.selectedCalendarId
			}})).save()
		}}),
		// btn({icon:'add', text: t('Reminder'), handler: _ => { console.warn('todo:reminder'); }}),
		hr(),
		btn({itemId:'paste',icon:'content_paste', text: t('Paste ','core'), handler: _ => {
			CalendarItem.paste(CalendarView.selectedCalendarId, this.contextMenuEmpty.dataSet.date)
		}})
	);

	protected selected: CalendarItem[] = []
	public viewModel: CalendarItem[] = []

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
				time.append(Format.time(item.start));
				if(item.dayLength > 1) {
					time.append(' - ',Format.time(item.end));
				}
			}
			div = E('div',
				E('em', item.title || '('+t(item.data.privacy!='public' ? 'Private' :'Nameless')+')'),
				...item.categoryDots,
				...item.icons,
				time
			)
		}
		if(item.key) {
			div.dataset.key = item.key;

		}

		if(client.user.calendarPreferences?.showTooltips)
			tooltip({
				style:{maxWidth:'60rem' ,wordBreak: 'break-word'},
				listeners: { 'render': ({target}) => {target.html = item.quickText}},
				target:div
			});


		return div.cls('allday',e.showWithoutTime)
			.cls('declined', item.isDeclined || item.isCancelled)
			.cls('undecided', item.needsAction)
			.cls('multiday', !e.showWithoutTime && item.dayLength > 1)
			.attr('tabIndex', 0)
			.on('click',(_ev)=> {
				this.selectItem(item);
			})
			//.on('mousedown', ev => ev.stopPropagation()) /* when enabled cant drag event in monthview */
			.on('contextmenu', ev => {

				this.selectItem(item);

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

	protected drawEventLine(e: CalendarItem, weekstart: DateTime) {
		if(!e.divs[weekstart.format('YW')]) {
			e.divs[weekstart.format('YW')] = this.eventHtml(e);
		}
		return e.divs[weekstart.format('YW')]
			.css(this.makestyle(e, weekstart))
			//.attr('style',this.makestyle(e, weekstart))
			.cls('continues', weekstart.diff(e.start).getTotalDays()! < 0)
	}

	private selectItem (item:CalendarItem) {
		this.focus(); // for catching keydown event
		// if not holding ctrl or shift, deselect
		while(this.selected.length) {
			Object.values(this.selected.shift()!.divs).forEach(el => el.cls('-selected'));
		}
		Object.values(item.divs).forEach(d => d.cls('+selected'));
		this.selected.push(item);
	}


	abstract renderView(): void;

	abstract goto(date:DateTime, days:number): void

	protected abstract populateViewModel(): void
}