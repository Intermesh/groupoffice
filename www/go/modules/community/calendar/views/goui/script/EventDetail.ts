import {
	btn, Button, checkbox,
	comp, Component, containerfield,
	DataSourceForm,
	datasourceform, DateInterval,
	DateTime, DisplayField, displayfield, EntityID, fieldset, Format, hr, mapfield, MaterialIcon, menu, Notifier,
	tbar, Toolbar,
	Window
} from "@intermesh/goui";
import {
	addbutton,
	client,
	DetailPanel,
	JmapDataSource,
	jmapds,
	linkbrowserbutton,
	RecurrenceField,
	entities
} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {calendarStore, statusIcons, t} from "./Index.js";
import {EventWindow} from "./EventWindow.js";


export class EventDetail extends DetailPanel<CalendarEvent> {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: DataSourceForm

	item?: CalendarItem
	recurrenceId?: string

	store: JmapDataSource
	private statusTbar: Toolbar
	private editBtn: Button;

	constructor() {
		super("CalendarEvent");
		this.title = t('Event');

		this.flex = "1";
		//this.height = 620;
		this.store = jmapds("CalendarEvent");

		const recurrenceField = displayfield({
			hideWhenEmpty: false,
			name: 'recurrenceRule', flex: 1,
			renderer(this: DisplayField, v) {
				return RecurrenceField.toText(v, this.dataSet.start);
			}
		});
		this.statusTbar = tbar({hidden:true, style:{alignItems:'space-between'}, cls: "border-top"},
			btn({itemId: 'accepted', text:t('Accept'), handler:()=>this.updateStatus('accepted')}),
			btn({itemId: 'tentative', text:t('Maybe'), handler:()=>this.updateStatus('tentative')}),
			btn({itemId: 'declined', text:t('Decline'), handler:()=>this.updateStatus('declined')})
		);

		const alertField = alertfield({
			hidden:true,
			listeners:{
			'change': (me, newValue) => {
				if(this.item?.data.id) {
					this.form.submit(); // hoppakee
				}
			}
		}});
		// alertUseDefault = checkbox({
		// 	hidden:true,
		// 	name:'useDefaultAlerts',
		// 	listeners: {
		// 		'setvalue': (_, newV) => {
		// 			if(newV) {alertField.useDefault = true;}
		// 		}
		// 	}})


		this.items.add(this.statusTbar);


		this.scroller.items.add(this.form = datasourceform({
				flex:1,
				dataSource: this.store,
				listeners: {
					'load': (_, data) => {
						const start = new DateTime(data.start);
						data.end = start.add(new DateInterval(data.duration)).addDays(data.showWithoutTime? -1 : 0).format('c');
						this.title = data.title;
						if(!data.recurrenceRule)
							recurrenceField.hidden = true;
						else
							recurrenceField.dataSet.start = start;

						if(data.participants && this.item!.calendarPrincipal) {
							this.statusTbar.show();
							this.pressButton(this.item!.calendarPrincipal.participationStatus);
						}
						if(data.useDefaultAlerts) {
							alertField.useDefault = true;
							delete data.alerts;
						}
					},
					'beforesave':(_, data) => {
						if(alertField.isModified() || !this.item?.data.id) {
							data.useDefaultAlerts = alertField.useDefault;
						}
					}
				}
			},
				comp({cls: "card flow"},
					fieldset({},
						displayfield({name: 'title',flex:1, label:t('Title')}),
						displayfield({name: 'calendarId', width:160, label:t('Calendar'), renderer: async (v) => {
							const c = await calendarStore.dataSource.single(v);
							return c ? c.name : t('Unknown');
						},listeners: {
							'setvalue': (me, v) => {
								if(v)
									calendarStore.dataSource.single(v).then(r => {
										if(!r) return;
										this.item!.cal = r;
										const d = this.item!.data.showWithoutTime ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
										alertField.setDefaultLabel(d)
									});
							}
						}  }),
						comp({cls:'hbox'},
							displayfield({label: t('Start'), name:'start',renderer:d=>Format.dateTime(d), flex:1}),
							displayfield({label:t('End'), name: 'end',renderer:d=>Format.dateTime(d), flex:1})
						),
						recurrenceField,
						displayfield({name: 'location', label:t('Location')}),
						displayfield({name:'description', tagName: "div", cls: "pad", escapeValue: false, renderer: (v, field) => Format.textToHtml(v)}),
						mapfield({name: 'participants',

							buildField: (v: any) => {
								const userIcon = v.roles?.owner ?
										'manage_accounts' : (v.kind == 'resource' ?
												'meeting_room' : (v.name ?
													'person' : 'contact_mail')
										),
									statusIcon = statusIcons[v.participationStatus] || v.participationStatus;

								let type = '';
								if(v.email == this.item?.calendarPrincipal?.email) {
									type = ' ('+(this.item!.principalId === client.user.id ? t('You') : t('This'))+')';
								}
								let name = v.name ? v.name + (v.email ? type+'<br>' + v.email :'') : v.email+type;

								return containerfield({cls:'hbox', style: {alignItems: 'center', cursor:'default'}},
									comp({tagName:'i',cls:'icon',html:userIcon, style:{margin:'0 8px'}}),
									comp({
										flex: '1 0 60%',
										html: name
									}),
									comp({tagName:'i',cls:'icon '+statusIcon[2],html:statusIcon[0],title:statusIcon[1], style:{margin:'0 8px'}}),
								);
							}

						}),
						hr(),
						alertField,
						mapfield({name: 'links', cls:'goui-pit',
							buildField: (v: any) => containerfield({flex:'1 0 100%',cls: 'flow'},
								btn({icon: "description", text: v.title, flex:'1', style:{textAlign:'left'}, handler() {
									client.downloadBlobId(v.blobId, v.title).catch((error) => {
										Notifier.error(error);
									})
								}})
							)
						})
					)
				)
			)

		);


		this.addLinks();
		this.addComments();
		this.addCustomFields();


		this.toolbar.items.add(

			this.editBtn = btn({
				icon: "edit",
				title: t("Edit"),
				handler: (button, ev) => {
					void this.item!.open();
				},
			}),


			addbutton(),

			linkbrowserbutton(),

			btn({
				icon: "more_vert",
				menu: menu({},
					btn({
						icon: "print",
						text: t("Print"),
						handler: () => {
							this.print();
						}
					}),

					hr(),

					btn({
						icon: "delete",
						text: t("Delete"),
						handler: () => {
							jmapds("CalendarEvent").confirmDestroy([this.entity!.id]);
						}
					})
				)
			})
		)
	}

	private pressButton(v:'accepted'|'declined'|'tentative') {
		this.statusTbar.items.forEach(btn => {
			btn.el.cls('pressed', btn.itemId === v);
		});

	}
	private updateStatus(v:'accepted'|'declined'|'tentative') {
		this.item!.updateParticipation(v);
		this.pressButton(v);
	}


	/**
	 * Load's an event from the data source without recurrenceId
	 * @param id
	 */
	async load(id:EntityID): Promise<this> {
		const r = await super.load(id);

		const item = (new CalendarItem({
			key: id + "",
			data:this.entity!
		}))

		await this.loadEvent(item);

		return r;
	}

	/**
	 * Loads an event from the CalendarItem view model with recurrence info
	 *
	 * @param ev
	 */
	async loadEvent(ev: CalendarItem) {
		this.item = ev;

		if (!ev.key) {

			this.item = ev;
			this.form.create(ev.data);

			this.scroller.hidden = false;
			this.disabled = false;

			this.statusTbar.items.replace(btn({
				text: t("Add"),
				handler: button => {
					const dlg = new EventWindow();
					dlg.show();
					dlg.loadEvent(this.item!);
				}
			}))

		} else {
			// await super.load(ev.data.id);

			this.form.findField('alerts')!.hidden = false;
			this.form.load(ev.data.id).then(() => {
				if(ev.recurrenceId) {
					const start = this.form.findField('start')!,
						end = this.form.findField('end')!;
					start.value = ev.start.format('Y-m-d\TH:i');
					start.trackReset();
					end.value = ev.end.format('Y-m-d\TH:i');
					end.trackReset();
				}
				if(ev.override) {
					for(const k in ev.patched) {
						const f = this.form.findField(k)
						if(f) {
							f.value = ev.patched[k as keyof CalendarEvent];
							f.trackReset();
						}
					}
				}
				this.item = ev;
			});
		}
		this.scroller.hidden = false;
		this.disabled = false;
	}

}

export class EventDetailWindow extends Window {

	view: EventDetail
	constructor() {
		super();
		this.title = t('Event');
		this.width = 440;
		this.height = 600;
		this.items.add(this.view = new EventDetail());
		//this.view.form.on('save', () => {this.close();})
	}

	loadEvent(ev: CalendarItem) {
		this.view.loadEvent(ev);
	}
}