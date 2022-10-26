import {comp, Component} from "@goui/component/Component.js";
import {tbar} from "@goui/component/Toolbar.js";
import {menu} from "@goui/component/menu/Menu.js";
import {btn} from "@goui/component/Button.js";
import {table} from "@goui/component/table/Table.js";
import {column} from "@goui/component/table/TableColumns.js";
import {jmapstore} from "@goui/jmap/JmapStore.js";
import {t} from "@goui/Translate.js";
//import {MonthView} from "./MonthView.js";
import {EventDialog} from "./EventDialog";
import {splitter} from "@goui/component/Splitter";
import {datepicker} from "@goui/component/picker/DatePicker";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {CardContainer, cards} from "@goui/component/CardContainer.js";

export class CalendarMain extends Component {

	// id = 'calendar'
	// title = t('Calendar')
	// cls = 'hbox'
	west: Component
	cards: CardContainer

	constructor() {
		super();
		this.cls = 'hbox fit';
		this.items.add(
			this.west = comp({tagName: 'aside', width: 226},
				datepicker(),
				tbar({},
					comp({html: 'Calendars'}),
					btn({icon: 'home'}),
					btn({icon: 'settings'}),
					btn({icon: 'done_all'})
				),
				table({
					store: jmapstore({
						entity:'Calendar',
						properties: ['id', 'name', 'color'],
						sort: [{property:'name'}]
					}),
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
					btn({icon: 'add', cls:'primary', text: t('Add'), handler: _ => (new EventDialog()).show() }),
					btn({icon: 'delete'}),
					btn({icon: 'refresh'}),
					btn({icon: 'settings'}),
					btn({cls: 'primary', icon: 'event'}),
					comp({cls: 'group hbox'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.cards.activeItem = 0}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.cards.activeItem = 0}),
						btn({icon: 'view_module', text: t('Month'), handler: b => this.cards.activeItem = 1})
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
					// new DayView(),
					new WeekView(),
					new MonthView()
				)
			)
		);
	}
}