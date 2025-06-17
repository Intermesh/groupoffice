import {
	btn, Button,
	CardContainer,
	cards,
	checkbox,
	comp,
	Component, datasourcestore,
	DatePicker,
	datepicker,
	DateTime,
	FunctionUtil, h3, hr, List,
	list,
	menu, router,
	splitter,
	tbar,
} from "@intermesh/goui";
import {MonthView} from "./MonthView.js";
import {WeekView} from "./WeekView.js";
import {calendarStore, categoryStore, t, ValidTimeSpan} from "./Index.js";
import {YearView} from "./YearView.js";
import {SplitView} from "./SpltView.js";
import {client, filterpanel, jmapds, modules} from "@intermesh/groupoffice-core";
import {CalendarView} from "./CalendarView.js";
import {CategoryWindow} from "./CategoryWindow.js";
import {Settings} from "./Settings.js";
import {ResourcesWindow} from "./ResourcesWindow.js";
import {CalendarAdapter} from "./CalendarAdapter.js";
import {ListView} from "./ListView.js";
import {CalendarItem} from "./CalendarItem.js";
import {CalendarList} from "./CalendarList.js";

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

		monthView.on('selectweek', (me, day) => {
			this.routeTo('week', day);
		});
		monthView.on('dayclick', (me,day) => {
			this.routeTo('day', day);
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
					showWeekNbs: false, // Wk nbs in datepicker are broken // client.user.calendarPreferences.showWeekNumbers,
					enableRangeSelect: true,
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
					this.calendarList = new CalendarList(),
					//this.stuffThatShouldGoIntoTheDavClientModuleWhenOverridesArePossible(),
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
					btn({
						cls:'not-medium-device',
						icon: 'inbox',
						title: t('Invitations'),
						hidden: !client.user.calendarPreferences?.autoAddInvitations,
						menu: menu({}, list({
							store:inviteStore,
							renderer: (r, row) => {
								const item = new CalendarItem({key:r.id + "", data:r}),
									owner = item.owner,
									press = function(b:Button,s:'accepted'|'tentative'|'declined') {
										b.el.cls('+pressed');
										item.updateParticipation(s,() => {
											inviteStore.reload();
										});

									};
								return [
									comp({cls:'pad'},
										comp({html:'<i style="color:#'+item.color+'">&bull;</i> <strong>'+r.title+'</strong><br><small>'+(owner?.name ?? owner?.email ?? t('Unknown owner'))+'</small>' }),
										h3({html: item.start.format('D j M')+' '+t('at')+' '+item.start.format('H:i')}),
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
						btn({icon: 'view_week', text: t('Week'), handler: _b => this.routeTo('week', this.date)}),
						btn({icon: 'view_module', text: t('Month'), handler: _b => this.routeTo('month', this.date)}),
						btn({icon: 'view_compact', text: t('Year'), handler: _b => this.routeTo('year', this.date)}),
						btn({icon: 'call_split', text: t('Split'), handler: _b => this.routeTo('split-5', this.date)}),
						btn({icon: 'list', text: t('List'), handler: _b => this.routeTo('list', this.date)}),
					),
					btn({icon:'view_agenda',cls: 'for-medium-device', flex:'0 0 auto', menu:menu({},
						btn({icon: 'view_day', text: t('Day'), handler: _b => this.routeTo('day', this.date)}),
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
									if(['day', 'week', 'month'].includes(view)) {
										this.openPDF(view);
									}
								}}),
								//'-',
								btn({icon: 'view_day', text: t('Day'), handler:() => { this.openPDF('day'); }}),
								btn({icon: 'view_week', text: t('Workweek'), handler:() => { this.openPDF('days'); }}),
								btn({icon: 'view_week', text: t('Week'), handler:() => { this.openPDF('week'); }}),
								btn({icon: 'view_module', text: t('Month'), handler:() => { this.openPDF('month'); }})
							)
						}),
						btn({icon:'meeting_room',hidden: !rights.mayChangeResources, text:t('Resources')+'…', handler: _ => { (new ResourcesWindow()).show()}})
					)})
				),
				this.cards = cards({flex: 1, activeItem:1, listeners: {render: m => this.applySwipeEvents(m)}},
					weekView,
					monthView,
					yearView,
					splitView,
					listView
				)
			)
		);
		this.timeSpan = client.user.calendarPreferences?.startView || 'month';
		this.date = new DateTime();
		// NOPE:router will call setSpan and render
		// calendar store load will call first view update
		this.calendarList.on('changevisible', (_,ids) => {
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
			initX = e.changedTouches[0].screenX,
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

	private stuffThatShouldGoIntoTheDavClientModuleWhenOverridesArePossible() {

		return list({
			store: datasourcestore({
				dataSource: jmapds('DavAccount'),
			}),
			listeners:{
				'render': (m)=>{
					m.store.load();
				}
			},
			renderer: (a) => {
				const list = new CalendarList(datasourcestore({
					dataSource: jmapds('Calendar'),
					queryParams: {filter: {isSubscribed: true, davaccountId: a.id}},
					sort: [{property: 'sortOrder'}, {property: 'name'}]
				}));

				list.on('changevisible', (l,ids) => {
					if(!this.initialized) {
						// after initial load. check for changed
						l.store.on('load', () => {
							this.view.update();
						});
						this.initialized = true;
					}
					this.applyInCalendarFilter(ids);
					this.updateView();
				});
				return [comp({},
					tbar({tagName: 'li', cls: 'dense'},
						comp({tagName: 'h3', html: a.name}),
						btn({icon: 'more_vert', menu: menu({},
							btn({icon: 'edit', text: t('Edit') + '…', handler: () => {
									// todo
								}
							}),
							btn({icon: 'sync', text: t('Sync'), handler: () => {
									client.jmap('DavAccount/sync', {accountId: a.id});
								}
							}))
						})
					),
					list
				)];
			}
		})
	}

	private export(calId: number) {

	}


	private applyInCalendarFilter(calendarIds: string[]) {
		const store = this.adapter.byType('event').store;

		//const calendarIds = Object.keys(this.inCalendars).filter(key => this.inCalendars[key])
		if(calendarIds.length) {
			Object.assign(store.queryParams.filter ||= {}, {
				inCalendars: calendarIds
			});
		} else {
			delete store.queryParams.filter?.inCalendars;
		}
	}

	private openPDF(type:string) {
		window.open(client.pageUrl('community/calendar/print/'+type+'/'+this.date.format('Y-m-d')));
	}

	private get view() : CalendarView {
		return this.cards.items.get(this.cards.activeItem) as CalendarView;
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
				'change': (_p, enabled) => {
					this.adapter.byType(key).enabled = enabled;
					jmapds('User').update(client.user.id, {calendarPreferences: {[key+'sAreVisible']: enabled}});
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
			listeners: {'render': me => { me.store.load() }},
			renderer: (data) => {
				return [checkbox({
					color: '#' + data.color,
					label: data.name,
					listeners: {
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