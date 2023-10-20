import {comp, Component} from "@intermesh/goui";
import {tbar} from "@intermesh/goui";
import {menu} from "@intermesh/goui";
import {btn} from "@intermesh/goui";
import {checkboxselectcolumn, column} from "@intermesh/goui";
import {t} from "@intermesh/goui";
import {EventDialog} from "./EventDialog";
import {splitter} from "@intermesh/goui";
import {DatePicker, datepicker} from "@intermesh/goui";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {CardContainer, cards} from "@intermesh/goui";
import {DateTime} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {CalendarDialog} from "./CalendarDialog.js";
import {list} from "@intermesh/goui";
import {checkbox} from "@intermesh/goui";
import {FunctionUtil} from "@intermesh/goui";
import {YearView} from "./YearView.js";
import {SplitView} from "./SpltView.js";
import {SubscribeWindow} from "./SubscribeWindow.js";

type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month' | 'year';
type ValidView = 'split' | 'merge';

export class Main extends Component {

	// id = 'calendar'
	// title = t('Calendar')
	// cls = 'hbox'
	west: Component
	cards: CardContainer
	cardMenu: Component
	currentText: Component

	date: DateTime

	timeSpan: ValidTimeSpan = 'month'
	viewType: ValidView = 'merge'

	picker: DatePicker
	spanAmount?: number // 2-7, 14, 21, 28

	monthView: MonthView

	constructor() {
		super();
		this.cls = 'hbox fit';
		this.date = new DateTime();

		this.items.add(
			this.west = comp({tagName: 'aside', width: 286},
				tbar({},
					btn({icon: 'add', cls:'primary filled', style:{width:'100%'}, text: t('Create event'), handler: _ => (new EventDialog()).show() })
				),
				this.picker = datepicker({
					showWeekNbs: false,
					enableRangeSelect: true,
					listeners: {
						'select': (_dp,date) => {
							this.date = date;
							this.updateView();
						},
						'select-range': (_dp, start, end) => {
							const days = Math.round((end.clone().setHours(12).getTime() - start.clone().setHours(12).getTime()) / 8.64e7)+1;
							this.date = start;
							if(days < 8) {
								this.setSpan('days', days);
							} else {
								this.setSpan('weeks', days);
							}

						}
					}
				}),
				tbar({cls:'dense'},
					comp({tagName:'h3', html: 'Calendars'}),
					btn({icon: 'add', menu: menu({},
						btn({text:t('Create calendar')+'…', handler: () => {
							const dlg = new CalendarDialog();
							dlg.form.create({});
							dlg.show();
						}}),
						btn({text: t('Subscribe to calendar')+'…', handler: () => {
							const d = new SubscribeWindow();
							d.show();
						}}),
						btn({text: t('Add calendar from link')+'…'})
					)}),
					btn({icon: 'done_all'})
				),
				list({
					store: calendarStore,
					cls:'check-list',
					// rowSelectionConfig: {
					// 	multiSelect: true,
					// 	listeners: {
					// 		'selectionchange': (tableRowSelect) => {
					//
					// 			// const noteBookIds = tableRowSelect.selected.map((index) => tableRowSelect.table.store.get(index).id);
					// 			//
					// 			// this.noteGrid.store.queryParams.filter = {
					// 			// 	noteBookId: noteBookIds
					// 			// };
					// 			//
					// 			// this.noteGrid.store.load();
					// 		}
					// 	}
					// },
					listeners: {
						'render': me => {me.store.load();}
					},
					//multiSelect: true,
					renderer: (data, row) => [checkbox({
						color: '#'+data.color,
						//style: 'padding: 0 8px',
						label: data.name,
						listeners: {
							'change': (p, newValue) => {
								// implement
								debugger;
								FunctionUtil.buffer(700, () => {
									//save isVisible
									//pending
								});
							}
						},
						buttons: [btn({icon: 'edit', handler: _ =>  {
								const dlg = new CalendarDialog();
								dlg.form.load(data.id);
								dlg.show();
							}
						})]
					})]


					// columns: [
					// 	//checkboxselectcolumn(),
					// 	column({id: 'name', header: 'Jaja'}),
					// 	column({id: 'id', renderer: (id: string) => {
					// 		return btn({icon: 'more_horiz', handler: _ =>  {
					// 			const dlg = new CalendarDialog();
					// 				dlg.form.load(id);
					// 				dlg.show();
					// 			}
					// 		})
					// 	}})
					// ]
				})
			),
			splitter({
				stateId: "calendar-splitter-west",
				resizeComponentPredicate: this.west
			}),
			comp({cls: 'vbox', flex: 1},
				tbar({},
					comp({cls: 'group'},
						btn({icon: 'keyboard_arrow_left', title: t('Previous'), handler: b => this.backward()}),
						btn({text: t('Today'), handler: b => {this.goto().updateView()}}),
						btn({icon: 'keyboard_arrow_right', title: t('Next'), handler: b => this.forward()}),
					),
					this.currentText = comp({tagName:'h3',text:t('Today'), style:{minWidth:'100px'}}),
					'->',
					this.cardMenu = comp({cls: 'group'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.setSpan('day', 1)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.setSpan('week', 7)}),
						btn({icon: 'view_module', text: t('Month'),handler: b => this.setSpan('month', 31)}),
						btn({icon: 'view_module', text: t('Year'),handler: b => this.setSpan('year', 365)}),
						btn({icon: 'call_split', text: t('Split'), handler: b => this.setView('split') }),
					),
					'->',
					// comp({cls:'group'},
					// 	btn({icon:'call_merge', cls:'active', handler: b => this.setView('merge') }),
					// 	btn({icon:'call_split', handler: b => this.setView('split')})
					// ),
					// '->',
					btn({icon: 'info'}),
					btn({
						icon: 'print', menu: menu({expandLeft: true},
							btn({icon: 'print', text: t('Print current view')}),
							btn({icon: 'print', text: t('Print count per category')}),
							//'-',
							btn({icon: 'view_day', text: t('Day')}),
							btn({icon: 'view_week', text: t('Week')}),
							btn({icon: 'view_module', text: t('Month')})
						)
					})
				),
				this.cards = cards({flex: 1},
					new WeekView(),
					this.monthView = new MonthView(),
					new YearView(this),
					new SplitView()
				)
			)
		);
		this.monthView.on('selectweek',(me, day) => {
			this.date = day;
			this.setSpan('week', 7);
		})
		// default start need to fetch from state?
		this.setSpan('week', 7);
	}

	goto(date = new DateTime()): this {
		this.date = date;
		return this;
	}

	onFilter() {
		//this.store.filter('calendarIds' , {});
	}

	backward() {
		this.forward(-1);
	}

	forward(value = 1) {
		switch(this.timeSpan) {
			case "day": this.date.addDays(value); break;
			case 'days':
			case 'weeks': this.date.addDays(value * this.spanAmount!); break;
			case 'week' : this.date.addDays(value*7); break;
			case 'month': this.date.addMonths(value); break;
			case 'year': this.date.addYears(value); break;
		}
		this.updateView();
	}

	setSpan(value: ValidTimeSpan, amount: number) {
		this.timeSpan = value;
		this.spanAmount = amount;
		this.viewType = 'merge';
		this.updateView();
	}

	setView(value: ValidView) {
		this.viewType = value;
		this.updateView();
	}

	updateView() {
		const tabs = ({
			// timeSpan : [cardIndex, cardnameIndex]
			'day': [0,0],
			'days': [0,-1],
			'week': [0,1],
			'weeks': [1,-1],
			'month': [1,2],
			'year': [2,3]
		})[this.timeSpan];

		if(this.viewType === 'split') {
			tabs[0] = 3; // use 3th tabpanel
			tabs[1] = 4; // use last tabmenu item
		}

		this.cardMenu.items.forEach(i=>i.el.cls('-active'));
		this.cards.activeItem = tabs[0];
		if(tabs[1] !== -1) {
			this.cardMenu.items.get(tabs[1])!.el.cls('+active');
		}
		const start = this.date.clone();
		let end;
		switch(this.timeSpan) {
			case 'year':
				this.spanAmount = undefined;
				this.currentText.text = start.format('Y');
				break;
			case 'month':
				this.spanAmount = undefined;
				this.currentText.text = (start.format('F'));
				break;
			case 'week':
				start.setWeekDay(0);
				this.currentText.text = 'Week '+start.format('W');
				break
			case 'weeks':
				start.setWeekDay(0);
				this.spanAmount = Math.ceil(this.spanAmount! / 7) * 7;
				end = start.clone().addDays(this.spanAmount - 1);
				this.currentText.text = 'W'+start.format('W') + ' - W' + end.format('W');
				break;
			case 'days':
				end = start.clone().addDays(this.spanAmount! - 1);
				this.currentText.text = start.format('j M') + ' - ' + end.format('j M');
				break;
			case 'day':
				this.currentText.text = this.date.format('j F');
				break;
		}
		this.picker.setValue(start, end);
		(this.cards.items.get(this.cards.activeItem) as WeekView)!.goto(start, this.spanAmount!);
	}
}