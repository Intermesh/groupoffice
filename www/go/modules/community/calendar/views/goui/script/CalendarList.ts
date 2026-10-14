import {
	browser,
	btn,
	checkbox, CheckboxField,
	checkboxselectcolumn, checkboxSelectColumnRenderer,
	column,
	comp,
	Component,
	ComponentEventMap,
	displayfield,
	fieldset,
	Format,
	hr,
	menu,
	menucolumn,
	select,
	selectallcheckboxfield,
	table,
	Table,
	tbar,
	win,
	Window
} from "@intermesh/goui";
import {calendarStore, Main, t} from "./Index.js";
import {CalendarView} from "./CalendarView.js";
import {CalendarWindow} from "./CalendarWindow.js";
import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {SubscribeWindow} from "./SubscribeWindow.js";
import {SubscribeWebCalWindow} from "./SubscribeWebCalWindow";
import {ViewWindow} from "./ViewWindow";
import {ResourceWindow} from "./ResourceWindow.js";

export interface CalendarListEventMap extends ComponentEventMap {
	changevisible: {ids: string[]}
}

/**
 * Calendar selection. Works like the notebook grid in notes: the checkboxes are the row selection and the
 * selected calendars are the visible ones. The selection is saved as "isVisible" on the calendars.
 */
export class CalendarList extends Component<CalendarListEventMap> {

	private inCalendars: {[key:string]:boolean} = {}

	list: Table
	store

	constructor(store = calendarStore){
		super()
		this.store = store;
		const rights = modules.get("community", "calendar")!.userRights;

		this.list = table({
			headers: false,
			cls: 'no-row-lines',
			store,
			groupBy: "group",
			groupByCollapsible: false,
			groupByRenderer: (groupBy) => groupBy ? `<h4>${groupBy.name.htmlEncode()}</h4>` : '',
			rowSelectionConfig: {
				multiSelect: true,
				listeners: {
					'selectionchange': ({selected}) => {
						this.onSelectionChange(selected.map(s => s.record));
					}
				}
			},
			columns: [
				checkboxselectcolumn({
					renderer: (columnValue, record, td, table1, storeIndex, column1) =>
					{
						const cb = checkboxSelectColumnRenderer(columnValue, record, td, table1, storeIndex, column1) as CheckboxField;
						cb.color = "#" + record.color;
						return cb;
					}
				}),
				column({
					id: 'name',
					resizable: false,
					renderer: (_v, record, td) => {


						const icon = record.webcalUri ? ' <i class="icon">web</i>' : '';
						return comp({
							tagName: 'span',
							title: record.description,
							html: String(record.name ?? '').htmlEncode() + icon
						});
					}
				}),
				menucolumn({
					menu: this.createRowMenu(rights)
				})
			],
			listeners: {
				'render': ({target}) => {
					target.store.on('load', ({records}) => {
						this.syncSelection(records);
					});
					void target.store.load();
				}
			}
		});

		this.items.add(store !== calendarStore ? comp() : tbar({
				cls: "table-aligned compact"
			},
			selectallcheckboxfield({list: this.list}),

			comp({tagName: 'h4', html: t('Calendars')}),
			"->",
			btn({
				icon: "home",
				handler:() => {
					const defaultId = client.user.calendarPreferences?.defaultCalendarId;
					if(defaultId) {
						const defaultCal = this.list.store.findById(defaultId);
						if(defaultCal) {
							this.select(this.list.store.findIndexById(defaultId));
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
					btn({
						hidden:!rights.mayChangeCalendars,
						icon: 'travel_explore',
						text: t('Add calendar from link') + '…',
						handler: () => {
							const d = new SubscribeWebCalWindow();
							d.show();
						}
					}),
					hr({hidden: !rights.mayChangeViews}),
					btn({hidden: !rights.mayChangeViews,
						icon: 'calendar_view_month',
						text: t('Compose new view')+ '…',
						handler: () => {
						const d = new ViewWindow();
						d.show();
					}})
				)
			})
		), this.list);
	}

	private visibleIds() {
		return Object.keys(this.inCalendars).filter(key => this.inCalendars[key]);
	}

	/**
	 * Set the selection from the "isVisible" property of the loaded calendars
	 */
	private syncSelection(records: any[]) {
		const oldLength = this.visibleIds().length;
		this.inCalendars = records.reduce((obj, item) => ({...obj, [item.id!]: !!item.isVisible}), {} as any);

		// inCalendars is already up to date so the resulting selectionchange doesn't save anything
		this.list.rowSelection!.replace(...records.filter(r => r.isVisible));

		this.updateSelectedCalendarId(records.filter(r => r.isVisible));

		const ids = this.visibleIds();
		if(oldLength !== ids.length || oldLength === 0) {
			this.fire('changevisible', {ids});
		}
	}

	private onSelectionChange(selected: any[]) {
		const changed: {[id:string]: boolean} = {};
		this.store.forEach(rec => {
			const on = selected.some(s => s.id == rec.id);
			if (!!this.inCalendars[rec.id] !== on) {
				changed[rec.id] = on;
			}
			this.inCalendars[rec.id] = on;
		});

		if (!Object.keys(changed).length) {
			// selection was set from the loaded isVisible values
			return;
		}

		this.updateSelectedCalendarId(selected);
		this.fire('changevisible', {ids: this.visibleIds()});

		for (const id in changed) {
			// origin: this list already shows the new value so it doesn't need to reload
			void jmapds('Calendar').update(id, {isVisible: changed[id]}, this).catch(e => Window.error(e));
		}
	}

	/**
	 * The calendar new events are added to must be one of the selected calendars that can be written to.
	 */
	private updateSelectedCalendarId(selected: any[]) {
		if (selected.some(s => s.id == CalendarView.selectedCalendarId && s.myRights?.mayWriteAll)) {
			return;
		}
		const writable = selected.find(s => s.myRights?.mayWriteAll);
		CalendarView.selectedCalendarId = writable ? writable.id : client.user.calendarPreferences?.defaultCalendarId;
	}

	private createRowMenu(rights: any) {
		const record = (target: Component) => this.list.store.get(target.dataSet.rowIndex)!;

		return menu({
				listeners: {
					show: ({target}) => {
						const data = record(target);
						target.findChild("sync")!.hidden = !data.davaccountId;
						target.findChild("reload")!.hidden = !data.webcalUri;

						const edit = target.findChild("edit")!;
						edit.hidden = !!data.davaccountId || !!(data.groupId && !rights.mayChangeResources && !data.myRights.mayAdmin);
						edit.disabled = !data.myRights.mayReadItems;

						const del = target.findChild("delete")!;
						del.hidden = !!data.davaccountId || !rights.mayChangeCalendars;
						del.disabled = !data.myRights.mayAdmin;

						target.findChild("sep-subscription")!.hidden = !rights.mayChangeCalendars;
						target.findChild("unsubscribe")!.hidden = data.id === client.user.calendarPreferences?.personalCalendarId;

						target.findChild("sep-file")!.hidden = !!data.groupId;
						target.findChild("export")!.hidden = !!data.groupId;
						target.findChild("import")!.hidden = !!data.groupId;
					}
				}
			},
			btn({itemId: "sync", icon: 'sync', text: t('Synchronize'), handler: (me) => {
				const data = record(me.parent!);
				this.list.mask();
				client.requestTimeout = 300000;
				client.jmap('DavAccount/sync', {accountId: data.davaccountId, collectionId: data.id}).then(() => {
					this.fire('changevisible', {ids: this.visibleIds()});
				}).catch((err) => {
					Window.error(err);
				}).finally(() => {
					this.list.unmask();
					client.requestTimeout = 30000;
				});
			}}),
			btn({itemId: "reload", icon: 'sync', text: t('Reload'), handler: (me) => {
				const data = record(me.parent!);
				this.list.mask();
				client.requestTimeout = 300000;
				client.jmap('Calendar/reload', {calendarId: data.id}).catch((err) => {
					Window.error(err);
				}).finally(() => {
					this.list.unmask();
					client.requestTimeout = 30000;
				});
			}}),
			btn({itemId: "edit", icon: 'edit', text: t('Edit') + '…', handler: async (me) => {
				const data = record(me.parent!);
				const dlg = data.groupId ? new ResourceWindow() : new CalendarWindow();
				await dlg.load(data.id);
				dlg.show();
			}}),
			btn({itemId: "delete", icon: 'delete', text: t('Delete', 'core', 'core') + '…', handler: _ => {
				void jmapds("Calendar").confirmDestroy([record(_.parent!).id]);
			}}),
			hr({itemId: "sep-subscription"}),
			btn({itemId: "unsubscribe", icon: 'remove_circle', text: t('Unsubscribe'), handler: (me) => {
				jmapds('Calendar').update(record(me.parent!).id, {isSubscribed: false}).catch(e => Window.error(e));
			}}),
			hr({itemId: "sep-file"}),
			btn({itemId: "export", icon: 'file_save', text: t('Export', 'core', 'core'), handler: (me) => {
				window.open(client.downloadUrl('community/calendar/calendar/' + record(me.parent!).id), '_blank');
			}}),
			btn({itemId: "import", icon: 'upload_file', text: t('Import', 'core', 'core') + '…', handler: async (me) => {
				const data = record(me.parent!);
				const files = await browser.pickLocalFiles(false, false, 'text/calendar');
				const blob = await client.upload(files[0]);

				this.importIcs(blob, data);
			}})
		);
	}

	/**
	 * Show only the calendar at the given store index
	 */
	private select(index: number) {
		this.list.rowSelection!.replace(this.list.store.get(index)!);
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