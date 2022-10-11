class CalendarMain extends Component {

	id = 'calendar'
	title = t('Calendar')
	cls = 'hbox'

	constructor() {
		super();
		this.setItems(
			comp({tagName: 'aside', width: 200},
				datepicker(),
				tbar({},
					comp({html: 'Calendars'}),
					btn({icon: 'home'}),
					btn({icon: 'settings'}),
					btn({icon: 'done_all'})
				),
				list({
					store: store({entity:'Calendar', properties: ['id', 'name', 'color'], sort: [{property:'name'}]}),
					listeners: {
						'render': me => {me.store.load();},
						'selectionchange': me => {this.test.setText('CHANGED!')}
					},
					multiSelect: true,
					columns: [
						{id: 'id'},
						{id: 'name'}
					]
				})
			),
			comp({layout: 'vbox', flex: true},
				tbar({},
					btn({icon: 'add', cls:'primary', text: t('Add'), handler: _ => (new EventDialog()).show() }),
					btn({icon: 'delete'}),
					btn({icon: 'refresh'}),
					btn({icon: 'settings'}),
					btn({cls: 'primary', icon: 'event'}),
					comp({cls: 'group'},
						btn({icon: 'view_day', text: t('Day')}),
						btn({icon: 'view_week', text: t('Week')}),
						btn({icon: 'view_module', text: t('Month')})
					),
					'->',
					btn({icon: 'info'}),
					btn({
						icon: 'print', menu: menu({},
							btn({icon: 'print', text: t('Print current view')}),
							btn({icon: 'print', text: t('Print count per category')}),
							'-',
							btn({icon: 'view_day', text: t('Day')}),
							btn({icon: 'view_week', text: t('Week')}),
							btn({icon: 'view_module', text: t('Month')})
						)
					})
				),
				comp({flex: true},
					// new DayView(),
					// new WeekView(),
					new MonthView()
				)
			)
		);
	}
}