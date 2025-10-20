import {
	btn, Button,
	CardContainer,
	cards,
	checkbox, column,
	comp,
	Component, datasourcestore, DateInterval,
	DatePicker,
	datepicker,
	DateTime, Format,
	FunctionUtil, h3, hr, List,
	list,
	menu, router,
	splitter, table,
	tbar,
} from "@intermesh/goui";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {calendarStore, categoryStore, t, ValidTimeSpan, viewStore} from "./Index.js";
import {YearView} from "./YearView.js";
import {SplitView} from "./SplitView.js";
import {client, filterpanel, jmapds, modules} from "@intermesh/groupoffice-core";
import {CalendarView} from "./CalendarView.js";
import {CategoryWindow} from "./CategoryWindow.js";
import {Settings} from "./Settings.js";
import {ResourcesWindow} from "./ResourcesWindow.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {ListView} from "./ListView.js";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarList} from "./CalendarList.js";
import {ViewWindow} from "./ViewWindow";

export class Main extends Component {

	// id = 'calendar'
	// title = t('Calendar')
	// cls = 'hbox'
	west: Component
	cards: CardContainer
	cardMenu: Component
	currentText: Component

	date: DateTime

	timeSpan!: ValidTimeSpan
	printCurrentBtn: Button

	inboxBtn: Button

	picker: DatePicker
	spanAmount?: number = 31 // 2-7, 14, 21, 28

	//eventStore: DataSourceStore<JmapDataSource<CalendarEvent>>
	adapter = new CalendarAdapter()

	private calendarList: CalendarList
	private categoryList: List

	/**
	 * Used to check if we're ready to populate the view. When initial render is done and a route is given the
	 * view will be loaded twice if we don't have this check.
	 *
	 * @private
	 */
	private initialized: boolean = false;

	constructor() {
		super();
		this.cls = 'hbox fit tablet-cards';

		this.adapter.onLoad = () => {
			this.view.update();
		};

		const inviteStore = datasourcestore({
			dataSource: jmapds('CalendarEvent'),
			filters: {'invites': {inbox:true}},
			sort: [{property: 'start'}]
		});

		const weekView= new WeekView(this.adapter),
			monthView = new MonthView(this.adapter),
			yearView = new YearView(this.adapter),
			splitView = new SplitView(this.adapter),
			listView = new ListView(this.adapter);

		monthView.on('selectweek', ( {day}) => {
			this.routeTo('week', day);
		});
		monthView.on('dayclick', ({day}) => {
			this.routeTo('day', day);
		});
		yearView.on('dayclick', ({day}) => {
			this.routeTo('day', day);
		})
		yearView.on('weekclick', ({week}) => {
			this.routeTo('week', week);
		});
		yearView.on('monthclick', ({month}) => {
			this.routeTo('month', month);
		});
		const rights = modules.get("community", "calendar")!.userRights;

		this.items.add(
			this.west = comp({tagName: 'aside', width: 274, cls:'scroll',style: {paddingTop:'1.2rem', minWidth: '27.4rem'}},
				tbar({cls: "for-medium-device"},
					'->',
					btn({
						title: t("Close"),
						icon: "close",
						handler: () => {
							this.west.el.cls('-active');
						}
					})
				),
				this.picker = datepicker({
					cls:'not-medium-device',
					showWeekNbs: client.user.calendarPreferences.showWeekNumbers,
					enableRangeSelect: true,
					listeners: {
						'select': ({date}) => {
							this.date = date!;
							this.updateView();
						},
						'select-range': ( {start, end}) => {
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
					this.renderViews(),
					this.calendarList = new CalendarList(),
					tbar({cls: 'dense'},comp({tagName: 'h3', html: t('Other')})),
					comp({tagName:'ul', cls:'goui check-list'}, ...this.renderAdapterBoxes()),
					tbar({cls: 'dense'},
						comp({tagName: 'h3', html: t('Categories','core','core')}),
						btn({
							hidden: !rights.mayChangeCategories,
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
				resizeComponent: this.west
			}),
			comp({cls: 'vbox active', flex: 1},
				tbar({},
					btn({cls: "for-medium-device", icon: "menu", handler: _ => {
						this.west.el.cls('!active');
					}}),
					btn({
						cls: 'not-medium-device',
						icon: 'add',
						title: t('New event'),
						handler: _ => (new CalendarItem({key:'',data:{
							start:(new DateTime).format('Y-m-d\TH:00:00.000'),
							title: t('New event'),
							showWithoutTime: client.user.calendarPreferences?.defaultDuration == null,
							duration: client.user.calendarPreferences?.defaultDuration ?? "P1D",
							calendarId: CalendarView.selectedCalendarId
						}})).save()
					}),
					this.inboxBtn = btn({
						cls:'not-medium-device accent filled',
						icon: 'inbox',
						title: t('Invitations'),
						menu: menu({}, list({
							store:inviteStore,
							renderer: (r, row) => {
								const item = new CalendarItem({key:r.id + "", data:r}),
									owner = item.owner,
									press = function(b:Button,s:'accepted'|'tentative'|'declined') {
										b.el.cls('+pressed');
										b.disabled;
										item.updateParticipation(s,() => {
											inviteStore.reload();
										});

									};
								return [
									comp({cls:'pad'},
										comp({html:'<i style="color:#'+item.color+'">&bull;</i> <strong>'+r.title+'</strong><br><small>'+(owner?.name ?? owner?.email ?? t('Unknown owner'))+'</small>' }),
										h3({html: item.start.format('D j M')+' '+t('at')+' '+Format.time(item.start)}),
									comp({cls:'group'},
										btn({itemId: 'accepted', text:t('Accept'), handler:b=>press(b,'accepted')}),
										btn({itemId: 'tentative', text:t('Maybe'), handler:b=>press(b,'tentative')}),
										btn({itemId: 'declined', text:t('Decline'), handler:b=>press(b,'declined')})
									),
									),

									hr()
								];
							}
						}))

					}),
					this.currentText = comp({tagName: 'h3', text: t('Today'), flex: '1 1 50%', style: {minWidth: '100px', fontSize: '1.8em'}}),
					//'->',
					this.cardMenu = comp({cls: 'group not-medium-device', flex:'0 0 auto'},
						btn({icon: 'view_day', text: t('Day'), handler: _b => this.routeTo('day', this.date)}),
						btn({icon: 'view_week', text: t('5 Days'), handler: _b => this.routeTo('days-5', this.date)}),
						btn({icon: 'view_week', text: t('Week'), handler: _b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: _b => this.routeTo('month', this.date)}),
						btn({icon: 'view_compact', text: t('Year'), handler: _b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: _b => this.routeTo('split-5', this.date)}),
						btn({icon: 'list', text: t('List'), handler: _b => this.routeTo('list', this.date)}),
					),
					btn({icon:'view_agenda',cls: 'for-medium-device', flex:'0 0 auto', menu:menu({},
						btn({icon: 'view_day', text: t('Day'), handler: _b => this.routeTo('day', this.date)}),
						btn({icon: 'view_week', text: t('5 days'), handler: _b => this.routeTo('days-5', this.date)}),
						btn({icon: 'view_week', text: t('Week'), handler: _b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: _b => this.routeTo('month', this.date)}),
						btn({icon: 'view_compact', text: t('Year'), handler: _b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: _b => this.routeTo('split-5', this.date)}),
						btn({icon: 'list', text: t('List'), handler: _b => this.routeTo('list', this.date)}),
					)}),
					comp({cls: 'group', flex: '1 1 50%', style:{justifyContent: 'end'}},
						btn({icon: 'keyboard_arrow_left', title: t('Previous'), allowFastClick:true, handler: b => this.backward()}),
						btn({
							icon: "today",
							title: t('Today'),
							handler: _b => {
								this.goto().updateView()
							}
						}),
						btn({icon: 'keyboard_arrow_right', title: t('Next'), allowFastClick:true, handler: b => this.forward()}),
					),
					btn({icon:'more_vert',cls: 'not-small-device', menu:menu({},
						btn({icon:'video_call',hidden:!client.user.isAdmin,text:t('Video meeting')+'…', handler: _ => {(new Settings()).openLoad()}}),
						btn({
							icon: 'print', text:t('Print'), menu: menu({},
								this.printCurrentBtn = btn({icon: 'print', text: t('Current view'), handler:() => {
									let view = this.timeSpan;
									if(['day', 'week', 'month', 'list'].includes(view)) {
										this.openPDF(view);
									}
								}}),
								//'-',
								btn({icon: 'view_list', text: t('List'), handler:() => { this.openPDF('list'); }}),
								btn({icon: 'view_day', text: t('Day'), handler:() => { this.openPDF('day'); }}),
								btn({icon: 'view_week', text: t('Workweek'), handler:() => { this.openPDF('days'); }}),
								btn({icon: 'view_week', text: t('Week'), handler:() => { this.openPDF('week'); }}),
								btn({icon: 'view_module', text: t('Month'), handler:() => { this.openPDF('month'); }})
							)
						}),
						btn({icon:'meeting_room',hidden: !rights.mayChangeResources, text:t('Resources')+'…', handler: _ => { (new ResourcesWindow()).show()}})
					)})
				),
				this.cards = cards({flex: 1, activeItem:1, listeners: {render: ({target}) => this.applySwipeEvents(target)}},
					weekView,
					monthView,
					yearView,
					splitView,
					listView
				)
			)
		);
		if(client.user.calendarPreferences?.startView) {
			const parts = client.user.calendarPreferences?.startView.split('-');
			this.setSpan(parts[0], parseInt(parts[1] ?? 0));
		} else {
			this.setSpan('month', 0);
		}
		this.date = new DateTime();
		// NOPE:router will call setSpan and render
		// calendar store load will call first view update
		this.calendarList.on('changevisible', ({ids}) => {
			if(!this.initialized) {
				// after initial load. check for changed
				calendarStore.on('load', () => {
					categoryStore.reload();
					this.view.update();
				});
				this.initialized = true;
			}
			this.applyInCalendarFilter(ids);
			this.updateView();
		});
		this.on('render', () => { inviteStore.load(); });
	}

	private applySwipeEvents(cards: CardContainer) {
		let initX = 0, initY = 0;
		cards.el.on('touchstart', e => {
			console.log('touchstart');
			initX = e.changedTouches[0].screenX;
			initY = e.changedTouches[0].screenY;
		}).on('touchend', e => {
			const diffX = initX - e.changedTouches[0].screenX,
				diffY = initY - e.changedTouches[0].screenY;
			if(Math.abs(diffY) > Math.abs(diffX) || Math.abs(diffX) < 10) return;
			if (diffX > 0) {
				this.forward();
			} else {
				this.backward();
			}
		})
	}


	private applyInCalendarFilter(calendarIds: string[]) {
		const store = this.adapter.byType('event').store;

		//const calendarIds = Object.keys(this.inCalendars).filter(key => this.inCalendars[key])

		Object.assign(store.queryParams.filter ||= {}, {
			inCalendars: calendarIds
		});

	}

	private openPDF(type:string) {
		if(type == "list") {

			let start = this.picker.value, end = start.clone();

			switch (this.timeSpan) {

				case 'days':
				case 'weeks':
					end.addDays(this.spanAmount!);
					break;

				case 'split':
					end.addDays(this.spanAmount!);
					break;
				/** @fallthough */
				case 'week':
					end.addDays(7);
					break;
				case 'month':
				case 'list':
					start.setDate(1);
					end.setDate(1).addMonths(1).addDays(-1);
					break;
				case 'year':
					start.setDate(1);
					start.setMonth(1);
					end = start.clone().addYears(1).addDays(-1);
					break;
			}


			window.open(client.pageUrl('community/calendar/printList/' + start.format('Y-m-d') + "/" + end.format('Y-m-d')));
		} else {
			window.open(client.pageUrl('community/calendar/print/' + type + '/' + this.date.format('Y-m-d')));
		}
	}

	private get view() : CalendarView {
		return this.cards.items.get(this.cards.activeItem) as CalendarView;
	}

	private renderViews() {
		const rights = modules.get("community", "calendar")!.userRights;
		return table({
			store: viewStore,
			emptyStateHtml:'',
			fitParent: true,
			headers:false,
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': ({selected}) => {
						if(selected[0]) {
							this.applyInCalendarFilter(selected[0].record.calendarIds??[]);
							if(selected[0].record.defaultView) {
								this.routeTo(selected[0].record.defaultView, this.date);
								// const parts = selected[0].record.defaultView.split('-');
								// if(!parts[1]) parts[1] = 0;
								// this.setSpan(parts[0], parseInt(parts[1] ?? 0));
							}
							this.updateView();
						}
					}
				}
			},
			listeners: {
				'render': ({target}) => {
					void target.store.load();
					this.calendarList.on('changevisible', () => { target.rowSelection?.clear() })
				},
			},
			columns:[
				column({
					id: "icon",
					width: 40,
					renderer: (value, record) => comp({tagName: "i", cls: "icon", text:'group'})
				}),
				column({id:'name'}),
				column({ width: 50,
					id: '-', renderer: (v, data) => btn({
						//hidden: !rights.mayChangeViews,
						icon: 'more_horiz', menu: menu({},
							btn({
								icon: 'edit', text: t('Edit'), disabled: !data.myRights.mayAdmin, handler: async _ => {
									const dlg = new ViewWindow();
									await dlg.load(data.id);
									dlg.show();
								}
							}),
							btn({
								icon: 'delete', text: t('Delete'), disabled: !data.myRights.mayAdmin, handler: async _ => {
									viewStore.dataSource.confirmDestroy([data.id]);
								}
							})
						)
					})
				})]
		});
	}

	private renderAdapterBoxes() {
		const boxes: any = {
			birthday:['#009c63', t('Birthdays')],
			task: 	['#7e472a',	t('Tasks')],
			holiday: ['#025d7b', t('Holidays')]
		};
		return Object.keys(boxes).map(key => comp({tagName:'li'}, checkbox({
			color: boxes[key][0], label: boxes[key][1], value: this.adapter.byType(key).enabled,
			listeners: {
				'change': ({newValue}) => {
					this.adapter.byType(key).enabled = newValue;
					jmapds('User').update(client.user.id, {calendarPreferences: {[key+'sAreVisible']: newValue}});
					this.updateView();
				}
			}
		})));
	}

	private buildCategoryFilter() {
		const rights = modules.get("community", "calendar")!.userRights;
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
			listeners: {'render': ({target}) => { void target.store.load() }},
			renderer: (data) => {
				return [checkbox({
					color: '#' + data.color,
					label: data.name,
					listeners: {
						'change': ({newValue}) => {
							if (newValue) {
								selected[data.id] = true;
							} else {
								delete selected[data.id];
							}
							selectionChange();
						}
					},
					buttons: [btn({
						hidden: !rights.mayChangeCategories,
						icon: 'more_horiz', menu: menu({},
							btn({icon:'edit', text: t('Edit'), disabled:!data.myRights.mayAdmin, handler: async _ => {
								const dlg = new CategoryWindow();
								await dlg.load(data.id);
								dlg.show();
							}}),
							btn({icon:'delete', text: t('Delete'), disabled:!data.myRights.mayAdmin, handler: async _ => {
									jmapds("CalendarCategory").confirmDestroy([data.id]);
							}})
						)
					})]
				})];
			}
		})
	}

	routeTo(view:string, date: DateTime) {
		router.goto("calendar/" + view + "/" + date.format('Y-m-d'));
	}

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
				if(this.spanAmount === 5) // workweek
					this.date.addDays(value * 7);
				else
					this.date.addDays(value * this.spanAmount!);
				break;
			case 'split':
				route += '-'+this.spanAmount;
				/** @fallthough */
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

		if(this.initialized)
			this.updateView();
	}

	updateView(buffered?:boolean) {
		const tabs = ({
			// timeSpan : [cardIndex, cardnameIndex]
			'day': [0, 0],
			'days': [0, -1],
			'week': [0, 2],
			'weeks': [1, -1],
			'month': [1, 3],
			'year': [2, 4],
			'split': [3,5],
			'list': [4,6]
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
				/** @fall-though intended */
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
				if(this.spanAmount === 5) {
					this.cardMenu.items.get(1)!.el.cls('+active');
					start.setWeekDay(0); // workweek. start monday
				}
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
			this.view.goto(start, this.spanAmount!);
		}
	}

	private bufferedUpdate = FunctionUtil.buffer(200, (start: DateTime)=> {
		this.view.goto(start, this.spanAmount!);
	})
}