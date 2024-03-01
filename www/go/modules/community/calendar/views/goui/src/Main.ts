import {
	browser,
	btn, Button,
	CardContainer,
	cards,
	checkbox,
	comp,
	Component,
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
import {EventWindow} from "./EventWindow.js";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {calendarStore, categoryStore, ValidTimeSpan} from "./Index.js";
import {CalendarWindow} from "./CalendarWindow.js";
import {YearView} from "./YearView.js";
import {SplitView} from "./SpltView.js";
import {SubscribeWindow} from "./SubscribeWindow.js";
import {client, filterpanel, jmapds} from "@intermesh/groupoffice-core";
import {CalendarView} from "./CalendarView.js";
import {CategoryWindow} from "./CategoryWindow.js";
import {Settings} from "./Settings.js";
import {ResourcesWindow} from "./ResourcesWindow.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {ListView} from "./ListView.js";
import {PreferencesWindow} from "./PreferencesWindow.js";

export class Main extends Component {

	// id = 'calendar'
	// title = t('Calendar')
	// cls = 'hbox'
	west: Component
	cards: CardContainer
	cardMenu: Component
	currentText: Component

	date: DateTime

	timeSpan: ValidTimeSpan
	printCurrentBtn: Button

	picker: DatePicker
	spanAmount?: number = 31 // 2-7, 14, 21, 28

	//eventStore: DataSourceStore<JmapDataSource<CalendarEvent>>
	private adapter = new CalendarAdapter()

	private calendarList: List
	private categoryList: List

	private visibleChanges: {[id:number]:boolean} = {};

	constructor() {
		super();
		this.cls = 'hbox fit tablet-cards';

		// jmapds('CalendarEvent');
		// this.eventStore = datasourcestore({
		// 	dataSource:jmapds('CalendarEvent'),
		// 	listeners: {
		// 		'load': () => { (this.cards.items.get(this.cards.activeItem) as CalendarView)!.update() }
		// 	}
		// 	//properties: ['title', 'start','duration','calendarId','showWithoutTime','alerts','recurrenceRule','id'],
		// });

		this.adapter.onLoad = () => { (this.cards.items.get(this.cards.activeItem) as CalendarView)!.update() };

		const weekView= new WeekView(this.adapter),
			monthView = new MonthView(this.adapter),
			yearView = new YearView(this.adapter),
			splitView = new SplitView(this.adapter),
			listView = new ListView(this.adapter);

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
			this.west = comp({tagName: 'aside', width: 304, cls:'scroll',style: {paddingTop:'1.2rem'}},
				tbar({cls: "for-medium-device"},
					'->',
					btn({
						title: t("Close"),
						icon: "close",
						handler: (button, ev) => {
							this.west.el.cls('-active');
						}
					})
				),
				this.picker = datepicker({
					cls:'not-medium-device',
					showWeekNbs: false, // Wk nbs in datepicker are broken // client.user.calendarPreferences.showWeekNumbers,
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
								let span = this.timeSpan == 'split' ? 'split' : 'days';
								this.routeTo(span+'-'+days, this.date);
							} else {

								this.routeTo('weeks-'+days, this.date);
								//this.setSpan('weeks', days);
							}

						}
					}
				}),
				comp({cls:'scroll'},
					tbar({cls: 'dense'},
						comp({tagName: 'h3', html: 'Calendars'}),
						btn({icon: 'done_all', handler: () => { this.calendarList.rowSelection!.selectAll();}}),
						btn({
							icon: 'more_vert', menu: menu({},
								btn({
									text: t('Create calendar') + '…', handler: () => {
										const dlg = new CalendarWindow();
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
						})
					),
					this.calendarList = this.buildCalendarFilter(),
					comp({tagName:'ul', cls:'goui check-list'},
						comp({tagName:'li'},this.birthdayCb),
						comp({tagName:'li'},this.tasksCb),
						comp({tagName:'li'},this.holidayCb),
					),
					tbar({cls: 'dense'},
						comp({tagName: 'h3', html: 'Categories'}),
						btn({
							icon: 'add', menu: menu({},
								btn({
									text: t('Create category') + '…', handler: () => {
										const dlg = new CategoryWindow();
										dlg.show();
										dlg.form.create({});

									}
								})
							)
						})
					),
					this.categoryList = this.buildCategoryFilter(),
					filterpanel({
						entityName: "CalendarEvent",
						store: this.adapter!.byType('event').store
					})
				)
			),
			splitter({
				stateId: "calendar-splitter-west",
				resizeComponentPredicate: this.west
			}),
			comp({cls: 'vbox active', flex: 1},
				tbar({},
					btn({cls: "for-medium-device", icon: "menu", handler: _ => {
						this.west.el.cls('!active');
					}}),
					btn({
						icon: 'add',
						title: t('New event'),
						handler: _ => (new EventWindow()).show()
					}),
					this.currentText = comp({tagName: 'h3', text: t('Today'), flex: '1 1 50%', style: {minWidth: '100px', fontSize: '2em'}}),
					//'->',
					this.cardMenu = comp({cls: 'group not-medium-device', flex:'0 0 auto'},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.routeTo('day', this.date)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: b => this.routeTo('month', this.date)}),
						btn({icon: 'view_module', text: t('Year'), handler: b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: b => this.routeTo('split-5', this.date)}),
						btn({icon: 'list', text: t('List'), handler: b => this.routeTo('list', this.date)}),
					),
					btn({icon:'view_agenda',cls: 'for-medium-device', flex:'0 0 auto', menu:menu({},
						btn({icon: 'view_day', text: t('Day'), handler: b => this.routeTo('day', this.date)}),
						btn({icon: 'view_week', text: t('Week'), handler: b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: b => this.routeTo('month', this.date)}),
						btn({icon: 'view_module', text: t('Year'), handler: b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: b => this.routeTo('split-5', this.date)}),
						btn({icon: 'list', text: t('List'), handler: b => this.routeTo('list', this.date)}),
					)}),
					comp({cls: 'group', flex: '1 1 50%', style:{justifyContent: 'end'}},
						btn({icon: 'keyboard_arrow_left', title: t('Previous'), allowFastClick:true, handler: b => this.backward()}),
						btn({
							text: t('Today'), handler: b => {
								this.goto().updateView()
							}
						}),
						btn({icon: 'keyboard_arrow_right', title: t('Next'), allowFastClick:true, handler: b => this.forward()}),
					),
					btn({icon:'more_vert',cls: 'not-small-device', menu:menu({expandLeft: true},
						btn({icon:'video_call',text:'Video meeting', handler: _ => {(new Settings()).openLoad()}}),
						btn({
							icon: 'print', text:t('Print'), menu: menu({expandLeft: true},
								this.printCurrentBtn = btn({icon: 'print', text: t('Current view'), handler:() => {
									let view = this.timeSpan;
									if(['day', 'week', 'month'].includes(view)) {
										this.openPDF(view);
									}
								}}),
								//'-',
								btn({icon: 'view_day', text: t('Day'), handler:() => { this.openPDF('day'); }}),
								btn({icon: 'view_week', text: t('5 days'), handler:() => { this.openPDF('days'); }}),
								btn({icon: 'view_week', text: t('Week'), handler:() => { this.openPDF('week'); }}),
								btn({icon: 'view_module', text: t('Month'), handler:() => { this.openPDF('month'); }})
							)
						}),
						btn({icon:'meeting_room', text:t('Resources'), handler: _ => { (new ResourcesWindow()).show()}}),
						btn({icon: 'settings', text: t('Preferences'), handler: _ => {
							const d=new PreferencesWindow();
							d.show();
							d.load(go.User.id);
						}})
					)})
				),
				this.cards = cards({flex: 1, activeItem:1},
					weekView,
					monthView,
					yearView,
					splitView,
					listView
				)
			)
		);
		this.timeSpan = client.user.calendarPreferences.startView || 'month';
		this.date = new DateTime();
		this.on('render', () => {this.updateView();});
	}

	private openPDF(type:string) {
		window.open(client.pageUrl('community/calendar/print/'+type+'/'+this.date.format('Y-m-d')));
	}

	private birthdayCb = checkbox({
		color: '#ff0000', label: t('Birthdays'),
		value: this.adapter.byType('birthday').enabled,
		listeners: {
			'change': (_p, newValue) => {
				this.adapterVisible('birthday', newValue);
			}
		}
	})

	private tasksCb = checkbox({
		color: '#0000ff', label: t('Tasks'),
		value: this.adapter.byType('task').enabled,
		listeners: {
			'change': (_p, newValue) => {
				this.adapterVisible('task', newValue);
			}
		}
	})

	private holidayCb = checkbox({
		color: '#009900', label: t('Holidays'),
		value: this.adapter.byType('holiday').enabled,
		listeners: {
			'change': (_p, newValue) => {
				this.adapterVisible('holiday', newValue);
			}
		}
	})

	private adapterVisible(name:string,enabled:boolean) {
		this.adapter.byType(name).enabled = enabled;
		jmapds('User').update(client.user.id, {calendarPreferences: {[name+'sAreVisible']: enabled}});
		this.updateView();
	}

	private saveAdapterVisbility = FunctionUtil.buffer(2000, () => {
		//save isVisible
		for(const id in this.visibleChanges) {
			calendarStore.dataSource.update(id, {isVisible:this.visibleChanges[id]});
		}
		this.visibleChanges = {};
	})

	private inCalendars: {[key:string]:boolean} = {}
	private buildCalendarFilter() {
		return list({
			store: calendarStore,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': (tableRowSelect) => {
						const calIds = tableRowSelect.selected.map((index) => calendarStore.get(index)?.id);
						if (calIds[0]) {
							CalendarView.selectedCalendarId = calIds[0];
						}
					}
				}
			},
			listeners: {'render': me => {
				me.store.on('load', (s,items)=> {
					const index = s.findIndex(c => c.id == CalendarView.selectedCalendarId);
					me.rowSelection!.selected = [index>0 ? index : 0];
					this.inCalendars = items.reduce((obj, item) => ({ ...obj, [item.id!]: item.isVisible }), {} as any);
				});

				me.store.load();
			}},
			renderer: (data, row, list, storeIndex) => {
				// if(data.isVisible) {
				// 	this.inCalendars[storeIndex] = true;
				// }
				return [checkbox({
					color: '#' + data.color,
					//style: 'padding: 0 8px',
					value: data.isVisible,
					label: data.name,
					listeners: {
						'render': (field) => {
							field.input.addEventListener("mousedown", (ev) => {
								ev.stopPropagation(); // stop lists row selector event
							});
						},
						'change': (p, newValue) => {
							const store = this.adapter.byType('event').store;
							this.inCalendars[data.id] = newValue;
							const calendarIds = Object.keys(this.inCalendars).filter(key => this.inCalendars[key])
							if(calendarIds.length) {
								Object.assign(store.queryParams.filter ||= {}, {
									inCalendars: calendarIds
								});
							} else {
								delete store.queryParams.filter?.inCalendars;
							}
							// FunctionUtil.buffer(1,() => {
							 	this.updateView();
							// })();
							this.visibleChanges[data.id] = newValue;
							this.saveSelectionChanges();
						}
					},
					buttons: [btn({
						icon: 'more_horiz', menu: menu({},
							btn({icon:'edit', text: t('Edit'), disabled:!data.myRights.mayAdmin, handler: async _ => {
									const dlg = new CalendarWindow();
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
		const selected: any = {},
			selectionChange = () => {
				const store = this.adapter.byType('event').store;
				const categoryIds = Object.keys(selected);

				if(categoryIds.length) {
					Object.assign(store.queryParams.filter ||= {}, {
						inCategories: categoryIds
					});
				} else {
					delete store.queryParams.filter?.inCategories;
				}
				this.updateView();
			};
		return list({
			store: categoryStore,
			cls: 'check-list',
			listeners: {'render': me => { me.store.load() }},
			renderer: (data, row) => {
				// if(data.isVisible && list.rowSelection) {
				// 	list.rowSelection.add(storeIndex);
				// }
				return [checkbox({
					color: '#' + data.color,
					//style: 'padding: 0 8px',
					//value: data.isVisible,
					label: data.name,
					listeners: {
						// 'render': (field) => {
						// 	field.el.addEventListener("mousedown", (ev) => {
						// 		ev.stopPropagation(); // stop lists row selector event
						// 	});
						// },
						'change': (p, newValue) => {
							if (newValue) {
								selected[data.id] = true;
							} else {
								delete selected[data.id];
							}
							selectionChange();
						}
					},
					buttons: [btn({
						icon: 'more_horiz', menu: menu({},
							btn({icon:'edit', text: t('Edit'), disabled:!data.myRights.mayAdmin, handler: async _ => {
								const dlg = new CategoryWindow();
								await dlg.load(data.id);
								dlg.show();
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
			case 'split':
				route += '-'+this.spanAmount;
			case 'week':
				this.date.addDays(value * 7);
				break;
			case 'month':
			case 'list':
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
		this.printCurrentBtn.disabled = !['day', 'week', 'month'].includes(value);
		this.spanAmount = amount;
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
			'year': [2, 3],
			'split': [3,4],
			'list': [4,5]
		})[this.timeSpan];

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
			case 'list':
				this.spanAmount = undefined;
				this.currentText.html = start.format('F ') + `<em> ${start.format('Y')}</em>`;
				break;
			case 'week':
				this.spanAmount = 7;
				// @fall-though intended
			case 'split':
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
				this.spanAmount = 1;
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