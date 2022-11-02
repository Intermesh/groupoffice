import {comp, Component} from "@goui/component/Component.js";
import {tbar} from "@goui/component/Toolbar.js";
import {menu} from "@goui/component/menu/Menu.js";
import {btn} from "@goui/component/Button.js";
import {table} from "@goui/component/table/Table.js";
import {column} from "@goui/component/table/TableColumns.js";
import {t} from "@goui/Translate.js";
//import {MonthView} from "./MonthView.js";
import {EventDialog} from "./EventDialog";
import {splitter} from "@goui/component/Splitter";
import {DatePicker, datepicker} from "@goui/component/picker/DatePicker";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {CardContainer, cards} from "@goui/component/CardContainer.js";
import {YearView} from "./YearView.js";
import {DateTime} from "@goui/util/DateTime.js";
import {calendarStore} from "./Index.js";

type ValidTimeSpan = 'day' | 'days' | 'week' | 'weeks' | 'month';

export class CalendarMain extends Component {

	// id = 'calendar'
	// title = t('Calendar')
	// cls = 'hbox'
	west: Component
	cards: CardContainer
	cardMenu: Component
	currentText: Component

	// time span to show
	start: DateTime

	timeSpan: ValidTimeSpan = 'month'
	picker: DatePicker
	spanAmount?: number // 2-7, 14, 21, 28

	constructor() {
		super();
		this.cls = 'hbox fit';
		this.start = new DateTime();

		this.items.add(
			this.west = comp({tagName: 'aside', width: 226},
				tbar({},
					btn({icon: 'add', cls:'primary', style:{width:'100%'}, text: t('Create event'), handler: _ => (new EventDialog()).show() })
				),
				this.picker = datepicker({
					listeners: {
						'select': (_dp,date) => {
							this.start = date;
							this.updateView();
						},
						'select-range': (_dp, start, end) => {
							const days = Math.round((end.clone().setHours(12).getTime() - start.clone().setHours(12).getTime()) / 8.64e7)+1;
							this.start = start;
							this.setSpan(days < 8 ? 'days' : 'weeks', days);
						}
					}
				}),
				tbar({},
					comp({html: 'Calendars'}),
					btn({icon: 'home'}),
					btn({icon: 'settings'}),
					btn({icon: 'done_all'})
				),
				table({
					store: calendarStore,
					listeners: {
						'render': me => {me.store.load();},
						//'selectionchange': me => {this.test.setText('CHANGED!')}
					},
					//multiSelect: true,
					columns: [
						column({id: 'id'}),
						column({id: 'name'})
					]
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
					btn({icon: 'refresh'}),
					btn({icon: 'settings'}),
					btn({cls: 'primary', icon: 'event'}),
					this.cardMenu = comp({cls: 'group'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.setSpan('day', 1)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.setSpan('week', 7)}),
						btn({icon: 'view_module', text: t('Month'),handler: b => this.setSpan('month', 31)}),
						btn({icon: 'view_comfy', text: t('Year'), handler: b => this.cards.activeItem = 2}),
					),
					'->',
					btn({icon: 'info'}),
					btn({
						icon: 'print', menu: menu({},
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
					new MonthView(),
					new YearView()
				)
			)
		);

		this.setSpan('month', 31);
	}

	backward() {
		this.forward(-1);
	}

	forward(value = 1) {
		switch(this.timeSpan) {
			case "day": this.start.addDays(value); break;
			case 'days':
			case 'weeks': this.start.addDays(value * this.spanAmount!); break;
			case 'week' : this.start.addDays(value*7); break;
			case 'month': this.start.addMonths(value); break;
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
		const start = this.start.clone();
		let end;
		switch(this.timeSpan) {
			case 'month':
				this.spanAmount = undefined;
				this.currentText.text = (this.start.format('F'));
				break;
			case 'week':
				this.currentText.text = 'Week '+this.start.format('W');
				start.setWeekDay(0);
				break
			case 'weeks':
				end = this.start.clone().addDays(this.spanAmount! - 1);
				this.currentText.text = 'W'+this.start.format('W') + ' - W' + end.format('W');
				break;
			case 'days':
				end = this.start.clone().addDays(this.spanAmount! - 1);
				this.currentText.text = this.start.format('j M') + ' - ' + end.format('j M');
				break;
			case 'day':
				this.currentText.text = this.start.format('j F');
				break;
		}
		this.picker.select(start, end);
		this.cards.items.get(this.cards.activeItem).goto(start, this.spanAmount);
	}
}