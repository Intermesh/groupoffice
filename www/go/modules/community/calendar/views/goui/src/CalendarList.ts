import {
	browser,
	btn,
	checkbox,
	comp,
	Component, ComponentEventMap,
	displayfield, fieldset, Format,
	FunctionUtil,
	hr, List,
	list,
	menu, ObservableListenerOpts, RowRenderer,
	select,
	tbar, win
} from "@intermesh/goui";
import {calendarStore, t} from "./Index.js";
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

	constructor(){
		super()
		this.items.add(tbar({cls: 'dense'},
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
					me.store.load().then(_c => {
						// after initial load. check for changed
						//console.log('calendars loaded');


						//this.applyInCalendarFilter();
						this.fire('changevisible', this, Object.keys(this.inCalendars).filter(key => this.inCalendars[key]));

						//this.updateView();

					});
				}},
			renderer: this.checkboxRenderer
		}));
	}

	checkboxRenderer: RowRenderer = (data, _row, _list, _storeIndex) => {
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
					btn({icon:'edit', text: t('Edit')+'…', disabled:!data.myRights.mayAdmin, handler: async _ => {
							const dlg = data.groupId ? new ResourceWindow() : new CalendarWindow();
							await dlg.load(data.id);
							dlg.show();
						}}),
					btn({icon:'delete', text: t('Delete','core','core')+'…', disabled:!data.myRights.mayAdmin, handler: async _ => {
							jmapds("Calendar").confirmDestroy([data.id]);
						}}),
					hr(),
					btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
							calendarStore.dataSource.update(data.id, {isSubscribed: false});
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
				store: calendarStore, valueField: 'id', textRenderer: (r: any) => r.name,
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
			calendarStore.dataSource.update(id, {isVisible:this.visibleChanges[id]});
		}
		this.visibleChanges = {};
	})
}