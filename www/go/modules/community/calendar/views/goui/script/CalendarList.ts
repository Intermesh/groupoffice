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
	menu,
	select,
	tbar, win, Window
} from "@intermesh/goui";
import {calendarStore, Main, t} from "./Index.js";
import {CalendarView} from "./CalendarView.js";
import {ResourceWindow} from "./ResourcesWindow.js";
import {CalendarWindow} from "./CalendarWindow.js";
import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {SubscribeWindow} from "./SubscribeWindow.js";
import {SubscribeWebCalWindow} from "./SubscribeWebCalWindow";

export interface CalendarListEventMap extends ComponentEventMap {
	changevisible: {ids: string[]}
}

export class CalendarList extends Component<CalendarListEventMap> {

	private inCalendars: {[key:string]:boolean} = {}
	private visibleChanges: {[id:number]:boolean} = {};

	list?: List
	store

	constructor(store = calendarStore){
		super()
		this.store = store;
		const rights = modules.get("community", "calendar")!.userRights;

		this.items.add(store !== calendarStore ? comp() :tbar({cls: 'dense'},
			checkbox({
				listeners: {
					change: ( {newValue}) => {
						this.select(-1,newValue);
					}
				}
			}),
			comp({tagName: 'h3', html: t('Calendars')}),
			//btn({icon: 'done_all', handler: () => { this.calendarList.rowSelection!.selectAll();}}),
			btn({
				icon: "home",
				handler:() => {
					const defaultId = client.user.calendarPreferences?.defaultCalendarId;
					if(defaultId) {

						const defaultCal = this.list!.store.findById(defaultId);

						if(defaultCal) {
							this.list!.rowSelection!.replace(defaultCal);
							const i = this.list!.store.findIndexById(defaultId);
							this.select(i);

						}
					}
				}
			}),
			btn({
				icon: 'more_vert', menu: menu({},
					btn({
						hidden: !rights.mayChangeCalendars,
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
					btn({icon: 'travel_explore',text: t('Add calendar from link') + '…', handler: () => {
						const d = new SubscribeWebCalWindow();
						d.show();
					}})
				)
			})
		), this.list = list({
			tagName: 'div',
			store,
			cls: 'check-list',
			rowSelectionConfig: {
				multiSelect: false,
				listeners: {
					'selectionchange': ({selected}) => {
						if (selected[0] && selected[0].record.myRights.mayWriteAll) {
							CalendarView.selectedCalendarId = selected[0].id;
						}
					}
				}
			},
			listeners: {'render': ({target}) => {
				const list = target;
				this.localGroup = document.createElement('ul');
					list.el.append(this.localGroup);

					list.store.on('load', ({target, records})=> {
						let record = target.find(c => c.id == CalendarView.selectedCalendarId);
						if(!record) {
							record = target.first();
						}
						if(record) {
							list.rowSelection!.add(record);
						}
						const oldLength = Object.values(this.inCalendars).filter(Boolean).length;
						this.inCalendars = records.reduce((obj, item) => ({ ...obj, [item.id!]: item.isVisible }), {} as any);
						const ids = Object.keys(this.inCalendars).filter(key => this.inCalendars[key]);
						if(oldLength !== ids.length || oldLength === 0) {
							this.fire('changevisible', {ids});
						}
					});
					target.store.load()
				}
			},
			renderer: this.checkboxRenderer.bind(this)
		}));
	}
	private davGroups: {[id:number]: HTMLElement} = {}
	private localGroup!: HTMLElement;

	checkboxRenderer(data: any, _row: HTMLElement, list: List, storeIndex: number) {

		const rights = modules.get("community", "calendar")!.userRights;
		const icon = data.webcalUri ? ' <i class="icon">web</i>' : '';
		return [checkbox({
			color: '#' + data.color,
			//style: 'padding: 0 8px',
			value: data.isVisible,
			label: data.name + icon,
			listeners: {
				'render': ({target}) => {
					target.input.addEventListener("mousedown", (ev) => {
						ev.stopPropagation(); // stop lists row selector event
					});
					target.input.addEventListener('contextmenu', (ev) => {
						ev.preventDefault();
						const m = menu({isDropdown:true},
							btn({text:t('Select all'),handler:()=>{this.select(-1,true)}}),
							btn({text:t('Select none'),handler:()=>{this.select(-1)}}),
							btn({text:t('Deselect others'),handler:()=>{this.select(storeIndex)}})
						);
						m.showAt(ev);
					})
				},
				'change': ( {newValue}) => {
					this.inCalendars[data.id] = newValue; // update to make sure it doesn't fire changevisible twice
					this.fire('changevisible', {ids:Object.keys(this.inCalendars).filter(key => this.inCalendars[key])});
					console.log(this.inCalendars);
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
							client.jmap('DavAccount/sync', {accountId:data.davaccountId,collectionId:data.id}).then(() => {
								this.fire('changevisible', {ids: Object.keys(this.inCalendars).filter(key => this.inCalendars[key])});
							}).catch((err) => {
								Window.error(err);
							}).finally(() => {
								cb.unmask();
								client.requestTimeout = 30000;
							});
						}
					}}),
					btn({icon:'sync', text: t('Reload'), hidden: !data.webcalUri, handler: (me) => {
						const cb = me.findAncestor((cmp) => cmp instanceof CheckboxField);
						if(cb) {
							cb.mask();
							client.requestTimeout = 300000;
							client.jmap('Calendar/reload', {calendarId:data.id}).catch((err) => {
								Window.error(err);
							}).finally(() => {
								cb.unmask();
								client.requestTimeout = 30000;
							});
						}
					}}),
					btn({icon:'edit', text: t('Edit')+'…', hidden: data.davaccountId || (data.groupId && !rights.mayChangeResources), disabled:!data.myRights.mayReadItems, handler: async _ => {
							const dlg = data.groupId ? new ResourceWindow() : new CalendarWindow();
							await dlg.load(data.id);
							dlg.show();
						}}),
					btn({icon:'delete', text: t('Delete','core','core')+'…', hidden: data.davaccountId || !rights.mayChangeCalendars, disabled:!data.myRights.mayAdmin, handler: async _ => {
						jmapds("Calendar").confirmDestroy([data.id]);
					}}),
					hr({hidden: !rights.mayChangeCalendars}),
					btn({icon: 'remove_circle', text: t('Unsubscribe'), handler() {
						jmapds('Calendar').update(data.id, {isSubscribed: false}).catch(e => Window.error(e))
					}}),
					hr({hidden:data.groupId}),
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

	private saveSelectionChanges = FunctionUtil.buffer(2000, () => {
		//save isVisible
		for(const id in this.visibleChanges) {
			jmapds('Calendar').update(id, {isVisible:this.visibleChanges[id]});
		}
		this.visibleChanges = {};
	})

	private select(index:number, all:boolean = false) {
		const rows = this.list!.el.querySelectorAll('li.data');
		this.list!.store.forEach((rec, rowIndex) => {
			const cb = rows[rowIndex].querySelector<HTMLInputElement>('input')!,
				on = (index == rowIndex || all);
			cb.checked = on;
			this.visibleChanges[rec.id] = on;
			this.inCalendars[rec.id] = on;
		});

		this.saveSelectionChanges();
		this.fire('changevisible', {ids: Object.keys(this.inCalendars).filter(key => this.inCalendars[key])});
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
						const main = this.findAncestor(cmp => cmp instanceof Main) as Main;
						if(main) main.adapter.byType('event').store!.load();
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
}