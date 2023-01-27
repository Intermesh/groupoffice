import {comp, Component} from "@goui/component/Component.js";
import {tbar} from "@goui/component/Toolbar.js";
import {menu} from "@goui/component/menu/Menu.js";
import {btn} from "@goui/component/Button.js";
import {checkboxselectcolumn, column} from "@goui/component/table/TableColumns.js";
import {t} from "@goui/Translate.js";
import {EventDialog} from "./EventDialog";
import {splitter} from "@goui/component/Splitter";
import {DatePicker, datepicker} from "@goui/component/picker/DatePicker";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {CardContainer, cards} from "@goui/component/CardContainer.js";
import {DateTime} from "@goui/util/DateTime.js";
import {calendarStore} from "./Index.js";
import {CalendarDialog} from "./CalendarDialog.js";
import {list} from "@goui/component/List.js";
import {checkbox} from "@goui/component/form/CheckboxField.js";
import {FunctionUtil} from "@goui/util/FunctionUtil.js";

type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month';

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
	picker: DatePicker
	spanAmount?: number // 2-7, 14, 21, 28

	monthView: MonthView

	constructor() {
		super();
		this.cls = 'hbox fit';
		this.date = new DateTime();

		this.items.add(
			this.west = comp({tagName: 'aside', width: 226},
				tbar({},
					btn({icon: 'add', cls:'primary', style:{width:'100%'}, text: t('Create event'), handler: _ => (new EventDialog()).show() })
				),
				this.picker = datepicker({
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
				tbar({},
					comp({html: 'Calendars'}),
					btn({icon: 'home'}),
					btn({icon: 'settings'}),
					btn({icon: 'done_all'})
				),
				list({
					store: calendarStore,
					cls:'calendar-list',
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
					renderer: data => [
						checkbox({
							flex:'1 0',
							style: {backgroundColor: '#'+data.color},
							width: 32,
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
							}
						}),
						btn({icon: 'more_horiz', handler: _ =>  {
								const dlg = new CalendarDialog();
								dlg.form.load(data.id);
								dlg.show();
							}
						})
					]

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
						btn({icon: 'keyboard_arrow_right', title: t('Next'), handler: b => this.forward()}),
					),
					this.currentText = comp({text:t('Today'), style:{minWidth:'100px'}}),
					btn({icon: 'delete'}),
					btn({cls: 'primary', icon: 'event'}),
					this.cardMenu = comp({cls: 'group'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.setSpan('day', 1)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.setSpan('week', 7)}),
						btn({icon: 'view_module', text: t('Month'),handler: b => this.setSpan('month', 31)})
					),
					'->',
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
					this.monthView = new MonthView()
					//new YearView()
				)
			)
		);
		this.monthView.on('selectweek',(day) => {
			this.date = day;
			this.setSpan('week', 7);
		})
		// default start need to fetch from state?
		this.setSpan('week', 7);
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
		}
		this.updateView();
	}

	setSpan(value: ValidTimeSpan, amount: number) {
		this.timeSpan = value;
		this.spanAmount = amount;
		this.updateView();
	}

	updateView() {
		const tabs = ({
			// timeSpan : [cardIndex, cardnameIndex]
			'day': [0,0],
			'days': [0,-1],
			'week': [0,1],
			'weeks': [1,-1],
			'month': [1,2]
		})[this.timeSpan];

		this.cardMenu.items.forEach(i=>i.el.cls('-active'));
		this.cards.activeItem = tabs[0];
		if(tabs[1] !== -1) {
			this.cardMenu.items.get(tabs[1]).el.cls('+active');
		}
		const start = this.date.clone();
		let end;
		switch(this.timeSpan) {
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
		this.cards.items.get(this.cards.activeItem).goto(start, this.spanAmount);
	}
}