class CalendarMain extends Component {

	id = 'calendar'
	title = t('Calendar')
	cls = 'hbox'

	constructor() {
		super();
		this.cls = 'hbox'
		this.items.add(
			comp({tagName: 'aside', width: 240},
				datepicker(),
				tbar({},
					comp({html:'Calendars'}),
					btn({icon:'home'}),
					btn({icon:'settings'}),
					btn({icon:'all_done'})
				),
				table({store:'Calendar', column: [
					column({id:'id'}),
					column({id:'name'})
				]})
			),
			comp({layout:'vbox',flex:true},
				tbar({},
					btn({icon:'add', text: t('Add')}),
					btn({icon:'delete'}),
					btn({icon:'refresh'}),
					btn({icon:'settings'}),
					btn({cls:'primary', icon:'event'}),
					comp({cls:'group'},
						btn({icon:'view_day', text: t('Day')}),
						btn({icon:'view_week', text: t('Week')}),
						btn({icon:'view_month', text: t('Month')})
					),
					comp({flex:true}),
					comp({icon:'info'}),
					comp({icon:'print', menu: menu({},
						btn({icon:'print', text: t('Print current view')}),
						btn({icon:'print', text: t('Print count per category')}),
						'-',
						btn({icon:'view_day', text: t('Day')}),
						btn({icon:'view_week', text: t('Week')}),
						btn({icon:'view_month', text: t('Month')})
					)})
				),
				cards({flex:true},
					new DayView(),
					new WeekView(),
					new MonthView()
				)
			),
		);
	}
}