import {
	browser,
	btn,
	checkbox, CheckboxField,
	comp,
	Component, ComponentEventMap,
	displayfield, fieldset, Format,
	FunctionUtil,
	hr, List,
	list,
	menu, ObservableListenerOpts, RowRenderer,
	select,
	tbar, win, Window
} from "@intermesh/goui";
import {calendarStore, categoryStore, t} from "./Index.js";
import {CalendarView} from "./CalendarView.js";
import {ResourceWindow} from "./ResourcesWindow.js";
import {CalendarWindow} from "./CalendarWindow.js";
import {client, jmapds} from "@intermesh/groupoffice-core";
import {SubscribeWindow} from "./SubscribeWindow.js";

export interface CalendarListEventMap<Type> extends ComponentEventMap<Type> {
	changevisible: (me: Type, ids: string[]) => false | void
}

export interface CalendarList extends Component {
	on<K extends keyof CalendarListEventMap<this>, L extends Function>(eventName: K, listener: Partial<CalendarListEventMap<this>>[K], options?: ObservableListenerOpts): L
	fire<K extends keyof CalendarListEventMap<this>>(eventName: K, ...args: Parameters<CalendarListEventMap<any>[K]>): boolean
}

export class CalendarList extends Component {

	private inCalendars: {[key:string]:boolean} = {}
	private visibleChanges: {[id:number]:boolean} = {};

	list?: List
	store

	constructor(store = calendarStore){
		super()
		this.store = store;
		this.items.add(store !== calendarStore ? comp() :tbar({cls: 'dense'},
			comp({tagName: 'h3', html: t('Calendars')}),
			//btn({icon: 'done_all', handler: () => { this.calendarList.rowSelection!.selectAll();}}),
			btn({
				icon: 'more_vert', menu: menu({},
					btn({
						icon: 'add',
						text: t('Create calendar') + '…', handler: () => {
							const dlg = new CalendarWindow();
							dlg.form.create({});
							dlg.show();
						}
					}),
					btn({
						icon: 'bookmark_added',
						text: t('Subscribe to calendar') + '…', handler: () => {
							const d = new SubscribeWindow();
							d.show();
						}
					}),
					btn({icon: 'travel_explore',text: t('Add calendar from link') + '…'})
				)
			})
		), this.list = list({
			tagName: 'div',
			store,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': (tableRowSelect) => {
						const calIds = tableRowSelect.getSelected().map((row) => row.record.id);
						if (calIds[0]) {
							CalendarView.selectedCalendarId = calIds[0];
						}
					}
				}
			},
			listeners: {'render': me => {
				this.localGroup = document.createElement('ul');
				me.el.append(this.localGroup);

				me.store.on('load', (s,items)=> {
					let record = s.find(c => c.id == CalendarView.selectedCalendarId);
					if(!record) {
						record = s.first();
					}
					if(record) {
						me.rowSelection!.add(record);
					}
					this.inCalendars = items.reduce((obj, item) => ({ ...obj, [item.id!]: item.isVisible }), {} as any);
				});
				me.store.load().then(_c => {
					// after initial load. check for changed
					//console.log('calendars loaded');


					//this.applyInCalendarFilter();
					this.fire('changevisible', this, Object.keys(this.inCalendars).filter(key => this.inCalendars[key]));

					//this.updateView();

				});
			}},
			renderer: this.checkboxRenderer.bind(this)
		}));
	}
	private davGroups: {[id:number]: HTMLElement} = {}
	private localGroup!: HTMLElement;

	checkboxRenderer(data: any, _row: HTMLElement, _list: List, _storeIndex: number) {
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
					this.inCalendars[data.id] = newValue;
					//this.applyInCalendarFilter();
					// FunctionUtil.buffer(1,() => {
					this.fire('changevisible', this, Object.keys(this.inCalendars).filter(key => this.inCalendars[key]));
					//this.updateView();
					// })();
					this.visibleChanges[data.id] = newValue;
					this.saveSelectionChanges();
				}
			},
			buttons: [btn({
				icon: 'more_horiz', menu: menu({},
					btn({icon:'sync', text: t('Synchronize'), hidden: !data.davaccountId, handler: (me) => {
						const cb = me.findAncestor((cmp) => cmp instanceof CheckboxField);
						if(cb) {
							cb.mask();
							client.requestTimeout = 300000;
							client.jmap('DavAccount/sync', {accountId:data.davaccountId}).then((response)=> {
								// reload should be automaticly
							}).catch((err) => {
								Window.error(err);
							}).finally(() => {
								cb.unmask();
								client.requestTimeout = 30000;
							});
						}
					}}),
					btn({icon:'edit', text: t('Edit')+'…', hidden: data.davaccountId, disabled:!data.myRights.mayAdmin, handler: async _ => {
							const dlg = data.groupId ? new ResourceWindow() : new CalendarWindow();
							await dlg.load(data.id);
							dlg.show();
						}}),
					btn({icon:'delete', text: t('Delete','core','core')+'…', hidden: data.davaccountId, disabled:!data.myRights.mayAdmin, handler: async _ => {
						jmapds("Calendar").confirmDestroy([data.id]);
					}}),
					hr(),
					btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
						jmapds('Calendar').update(data.id, {isSubscribed: false}).catch(e => Window.error(e))
					}}),
					hr(),
					btn({icon:'file_save',hidden:data.groupId, text: t('Export','core','core'), handler: _ => { client.getBlobURL('community/calendar/calendar/'+data.id).then(window.open) }}),
					btn({icon:'upload_file',hidden:data.groupId, text:t('Import','core','core')+'…', handler: async ()=> {
							const files = await browser.pickLocalFiles(false,false,'text/calendar');
							const blob = await client.upload(files[0]);

							this.importIcs(blob, data);
						}})
				)
			})]
		})];
	}

	private importIcs(blob: any, data:any) {
		const calendarSelect = select({
				label: t('Calendar'), name: 'calendarId', required: true, flex: '1 30%',value:data.id,
				store: this.store, valueField: 'id', textRenderer: (r: any) => r.name,
			}),
			uidCheckbox = checkbox({name:'ignoreUID', label: t('Import events as new (Ignore UID)')}),
			statusReport = comp({hidden:true}),
			bbar = tbar({},'->',btn({text:t('Start'), handler: (b) => {
					w.mask();
					b.disabled = true;
					client.jmap("CalendarEvent/import", {
						blobIds:[blob.id],
						calendarId:calendarSelect.value,
						ignoreUid: uidCheckbox.value
					}, 'pIcs').then(r => {
						w.unmask();
						//this.adapter.byType('event').store!.load();
						let statuses = [];
						if(r.saved) {
							statuses.push(displayfield({icon: 'done', cls:'green',value: t('Imported %s events successful.').replace('%s', r.saved)}));
						}
						if(r.skipped > 0) {
							statuses.push(displayfield({icon: 'remove_done', cls:'orange',value: t('Skipped %s event(s) because UID already existed.').replace('%s', r.skipped)}));
						}
						if(r.failed > 0) {
							statuses.push(
								displayfield({icon: 'cancel', cls:'red',value: t('%s events were not imported.').replace('%s', r.failed)}),
								displayfield({label:t('Reasons'), html: '<ul><li>'+r.failureReasons.join('<li>')+'</ul>'})
							);
						}
						calendarSelect.hidden = true;
						statusReport.hidden = false;
						statusReport.items.add(...statuses);
						bbar.hidden = true;
						uidCheckbox.hidden = true;
					}).catch(e => {
						alert(t('ICS file could not be imported, error: ') + e.message);
						w.close();
					});
				}}));
		const w = win({title:'Import ICS file', width: 500},
			fieldset({cls:'pad flow'},
				comp({cls:'pad',html:t('Import')+ ' '+blob.name + ' ('+Format.fileSize(blob.size)+')'}),
				calendarSelect, uidCheckbox,statusReport
			),
			bbar
		);
		w.show();

	}

	saveSelectionChanges = FunctionUtil.buffer(2000, () => {
		//save isVisible
		for(const id in this.visibleChanges) {
			jmapds('Calendar').update(id, {isVisible:this.visibleChanges[id]});
		}
		//categoryStore.setFilter('calendars', {calendarId: this.visibleChanges}).load();
		this.visibleChanges = {};
	})
}