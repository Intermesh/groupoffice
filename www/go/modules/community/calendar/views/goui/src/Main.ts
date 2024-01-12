import {
	browser,
	btn,
	CardContainer,
	cards,
	checkbox,
	comp,
	Component,
	DataSourceStore,
	datasourcestore,
	DatePicker,
	datepicker,
	DateTime,
	FunctionUtil, List,
	list,
	menu, router,
	splitter,
	t,
	tbar
} from "@intermesh/goui";
import {EventDialog} from "./EventDialog";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {calendarStore,categoryStore} from "./Index.js";
import {CalendarDialog} from "./CalendarDialog.js";
import {YearView} from "./YearView.js";
import {SplitView} from "./SpltView.js";
import {SubscribeWindow} from "./SubscribeWindow.js";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {CalendarView} from "./CalendarView.js";
import {CalendarEvent} from "./CalendarItem.js";
import {CategoryDialog} from "./CategoryDialog.js";
import {Settings} from "./Settings.js";
import {ResourcePanel} from "./ResourcesPanel.js";

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
	spanAmount?: number = 31 // 2-7, 14, 21, 28

	eventStore: DataSourceStore<JmapDataSource<CalendarEvent>>

	private calendarList: List
	private categoryList: List

	private visibleChanges: {[id:number]:boolean} = {};

	constructor() {
		super();
		this.cls = 'hbox fit';
		this.date = new DateTime();

		jmapds('CalendarEvent');
		this.eventStore = datasourcestore({
			dataSource:jmapds('CalendarEvent'),
			listeners: {
				'load': () => { (this.cards.items.get(this.cards.activeItem) as CalendarView)!.update() }
			}
			//properties: ['title', 'start','duration','calendarId','showWithoutTime','alerts','recurrenceRule','id'],
		});

		const weekView= new WeekView(this.eventStore),
			monthView = new MonthView(this.eventStore),
			yearView = new YearView(this.eventStore),
			splitView = new SplitView(this.eventStore);
		monthView.on('selectweek', (me, day) => {
			this.routeTo('week', day);
		});
		yearView.on('dayclick', (me,day) => {
			this.routeTo('day', day);
		})
		yearView.on('weekclick', (me,weekDay) => {
			this.routeTo('week', weekDay);
		});
		yearView.on('monthclick', (me,day) => {
			this.routeTo('month', day);
		});

		this.items.add(
			this.west = comp({tagName: 'aside', width: 374},
				tbar({},
					btn({
						icon: 'add',
						cls: 'primary filled',
						style: {width: '100%'},
						text: t('Create event'),
						handler: _ => (new EventDialog()).show()
					})
				),
				this.picker = datepicker({
					showWeekNbs: false,
					enableRangeSelect: true,
					withoutFooter: true,
					listeners: {
						'select': (_dp, date) => {
							this.date = date!;
							this.updateView();
						},
						'select-range': (_dp, start, end) => {
							const days = Math.round((end!.clone().setHours(12).getTime() - start!.clone().setHours(12).getTime()) / 8.64e7) + 1;
							this.date = start!;
							if (days < 8) {
								this.routeTo('days-'+days, this.date);
								//this.setSpan('days', days);
							} else {
								this.routeTo('weeks-'+days, this.date);
								//this.setSpan('weeks', days);
							}

						}
					}
				}),
				tbar({cls: 'dense'},
					comp({tagName: 'h3', html: 'Calendars'}),
					btn({
						icon: 'add', menu: menu({},
							btn({
								text: t('Create calendar') + '…', handler: () => {
									const dlg = new CalendarDialog();
									dlg.form.create({});
									dlg.show();
								}
							}),
							btn({
								text: t('Subscribe to calendar') + '…', handler: () => {
									const d = new SubscribeWindow();
									d.show();
								}
							}),
							btn({text: t('Add calendar from link') + '…'})
						)
					}),
					btn({icon: 'done_all', handler: () => { this.calendarList.rowSelection!.selectAll();}})
				),
				this.calendarList = this.buildCalendarFilter(),
				tbar({cls: 'dense'},
					comp({tagName: 'h3', html: 'Categories'}),
					btn({
						icon: 'add', menu: menu({},
							btn({
								text: t('Create category') + '…', handler: () => {
									const dlg = new CategoryDialog();
									dlg.show();
									dlg.form.create({});

								}
							})
						)
					}),
					btn({icon: 'done_all', handler: () => { this.categoryList.rowSelection!.selectAll();}})
				),
				this.categoryList = this.buildCategoryFilter()
			),
			splitter({
				stateId: "calendar-splitter-west",
				resizeComponentPredicate: this.west
			}),
			comp({cls: 'vbox', flex: 1},
				tbar({},

					this.currentText = comp({tagName: 'h3', text: t('Today'), flex: '1 1 50%', style: {minWidth: '100px'}}),
					//'->',
					this.cardMenu = comp({cls: 'group', flex:'0 0 auto'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.routeTo('day', this.date)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: b => this.routeTo('month', this.date)}),
						btn({icon: 'view_module', text: t('Year'), handler: b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: b => this.setView('split')}),
					),
					//'->',
					// comp({cls:'group'},
					// 	btn({icon:'call_merge', cls:'active', handler: b => this.setView('merge') }),
					// 	btn({icon:'call_split', handler: b => this.setView('split')})
					// ),
					// '->',
					comp({cls: 'group', flex: '1 1 50%', style:{justifyContent: 'end'}},
						btn({icon: 'keyboard_arrow_left', title: t('Previous'), allowFastClick:true, handler: b => this.backward()}),
						btn({
							text: t('Today'), handler: b => {
								this.goto().updateView()
							}
						}),
						btn({icon: 'keyboard_arrow_right', title: t('Next'), allowFastClick:true, handler: b => this.forward()}),
					),
					btn({
						icon: 'print', menu: menu({expandLeft: true},
							btn({icon: 'print', text: t('Print current view')}),
							btn({icon: 'print', text: t('Print count per category')}),
							//'-',
							btn({icon: 'view_day', text: t('Day')}),
							btn({icon: 'view_week', text: t('Week')}),
							btn({icon: 'view_module', text: t('Month')})
						)
					}),
					btn({icon:'more_vert', menu:menu({},
						btn({icon:'video_call',text:'Video meeting', handler: _ => {(new Settings()).show()}}),
						btn({icon:'meeting_room', text:t('Resources'), handler: _ => {(new ResourcePanel()).show()}})
					)})
				),
				this.cards = cards({flex: 1},
					weekView,
					monthView,
					yearView,
					splitView
				)
			)
		);

	}

	private buildCalendarFilter() {
		return list({
			store: calendarStore,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					'selectionchange': (tableRowSelect) => {

						const calendarIds = tableRowSelect.selected.map((index) => calendarStore.get(index)?.id);
						if(calendarIds.length) {
							Object.assign(this.eventStore.queryParams.filter ||= {}, {
								inCalendars: calendarIds
							});
						} else {
							delete this.eventStore.queryParams.filter?.inCalendars;
						}

						//this.eventStore.load();
						this.updateView();
					}
				}
			},
			listeners: {'render': me => { me.store.load() }},
			renderer: (data, row, list, storeIndex) => {
				if(data.isVisible && list.rowSelection) {
					list.rowSelection.add(storeIndex);
				}
				return [checkbox({
					color: '#' + data.color,
					//style: 'padding: 0 8px',
					value: data.isVisible,
					label: data.name,
					listeners: {
						'render': (field) => {
							field.el.addEventListener("mousedown", (ev) => {
								ev.stopPropagation(); // stop lists row selector event
							});
						},
						'change': (p, newValue) => {
							if (newValue) {
								list.rowSelection!.add(storeIndex);
							} else {
								list.rowSelection!.remove(storeIndex);
							}
							this.visibleChanges[data.id] = newValue;
							this.saveSelectionChanges();
						}
					},
					buttons: [btn({
						icon: 'more_horiz', menu: menu({},
							btn({icon:'edit', text: t('Edit'), disabled:!data.myRights.mayAdmin, handler: async _ => {
									const dlg = new CalendarDialog();
									await dlg.load(data.id);
									dlg.show();
								}}),
							btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
									calendarStore.dataSource.update(data.id, {isSubscribed: false});
								}}),
							btn({icon:'import_export', text:t('Import'), handler: async ()=> {
									const files = await browser.pickLocalFiles();
									const blob = await client.upload(files[0]);
									client.jmap("CalendarEvent/parse", {blobIds:[blob.id]}, 'pIcs').then(r => {
										// bult events.
										// set calendarId op alle / flikker uid eruit
										// call /set create met events
									});
								}})
						)
					})]
				})];
			}
		});
	}

	private buildCategoryFilter() {
		return list({
			store: categoryStore,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					'selectionchange': (tableRowSelect) => {

						const categoryIds = tableRowSelect.selected.map((index) => categoryStore.get(index)?.id);
						debugger;
						if(categoryIds.length) {
							Object.assign(this.eventStore.queryParams.filter ||= {}, {
								inCategories: categoryIds
							});
						} else {
							delete this.eventStore.queryParams.filter?.inCategories;
						}

						//this.eventStore.load();
						this.updateView();
					}
				}
			},
			listeners: {'render': me => { me.store.load() }},
			renderer: (data, row, list, storeIndex) => {
				// if(data.isVisible && list.rowSelection) {
				// 	list.rowSelection.add(storeIndex);
				// }
				return [checkbox({
					//color: '#' + data.color,
					//style: 'padding: 0 8px',
					//value: data.isVisible,
					label: data.name,
					listeners: {
						'render': (field) => {
							field.el.addEventListener("mousedown", (ev) => {
								ev.stopPropagation(); // stop lists row selector event
							});
						},
						'change': (p, newValue) => {
							if (newValue) {
								list.rowSelection!.add(storeIndex);
							} else {
								list.rowSelection!.remove(storeIndex);
							}
						}
					},
					buttons: [btn({
						icon: 'more_horiz', menu: menu({},
							btn({icon:'edit', text: t('Edit'), disabled:!data.myRights.mayAdmin, handler: async _ => {
								const dlg = new CategoryDialog();
								await dlg.load(data.id);
								dlg.show();
							}}),
							btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
								calendarStore.dataSource.update(data.id, {isSubscribed: false});
							}})
						)
					})]
				})];
			}
		})
	}

	routeTo(view:string, date: DateTime) {
		router.goto("calendar/"+view+"/"+date.format('Y-m-d'));
	}

	saveSelectionChanges = FunctionUtil.buffer(2000, () => {
		//save isVisible
		for(const id in this.visibleChanges) {
			calendarStore.dataSource.update(id, {isVisible:this.visibleChanges[id]});
		}
		this.visibleChanges = {};
	})

	goto(date = new DateTime()): this {
		this.date = date;
		return this;
	}

	backward() {
		this.forward(-1);
	}

	forward(value = 1) {
		let route = this.timeSpan;
		switch (this.timeSpan) {
			case "day":
				this.date.addDays(value);
				break;
			case 'days':
			case 'weeks':
				route += '-'+this.spanAmount;
				this.date.addDays(value * this.spanAmount!);
				break;
			case 'week' :
				this.date.addDays(value * 7);
				break;
			case 'month':
				this.date.addMonths(value);
				break;
			case 'year':
				this.date.addYears(value);
				break;
		}
		// set path silent to buffer the update
		//router.suspendEvent = true;
		router.setPath("calendar/"+route+"/"+this.date.format('Y-m-d'));
		//todo: enable this line when the router is no longer broken
		//this.updateView(true);
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

	updateView(buffered?:boolean) {
		const tabs = ({
			// timeSpan : [cardIndex, cardnameIndex]
			'day': [0, 0],
			'days': [0, -1],
			'week': [0, 1],
			'weeks': [1, -1],
			'month': [1, 2],
			'year': [2, 3]
		})[this.timeSpan];

		if (this.viewType === 'split') {
			tabs[0] = 3; // use 3th tabpanel
			tabs[1] = 4; // use last tabmenu item
		}

		this.cardMenu.items.forEach(i => i.el.cls('-active'));
		this.cards.activeItem = tabs[0];
		if (tabs[1] !== -1) {
			this.cardMenu.items.get(tabs[1])!.el.cls('+active');
		}
		const start = this.date.clone();
		let end;
		switch (this.timeSpan) {
			case 'year':
				this.spanAmount = undefined;
				this.currentText.text = start.format('Y');
				break;
			case 'month':
				this.spanAmount = undefined;
				this.currentText.html = start.format('F ') + `<em> ${start.format('Y')}</em>`;
				break;
			case 'week':
				start.setWeekDay(0);
				this.currentText.html = start.format('F ') + `<em> ${start.format('Y')}</em>`;
				break
			case 'weeks':
				start.setWeekDay(0);
				this.spanAmount = Math.ceil(this.spanAmount! / 7) * 7;
				end = start.clone().addDays(this.spanAmount - 1);
				this.currentText.text = 'W' + start.format('W') + ' - W' + end.format('W');
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

		if(buffered) {
			// for the fast previous/forward clickers
			this.bufferedUpdate(start);
		} else {
			(this.cards.items.get(this.cards.activeItem) as CalendarView)!.goto(start, this.spanAmount!);
		}
	}

	private bufferedUpdate = FunctionUtil.buffer(200, (start: DateTime)=> {
		(this.cards.items.get(this.cards.activeItem) as CalendarView)!.goto(start, this.spanAmount!);
	})
}