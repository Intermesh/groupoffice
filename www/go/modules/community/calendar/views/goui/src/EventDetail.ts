import {
	btn, checkbox,
	comp, Component, containerfield,
	DataSourceForm,
	datasourceform, DateInterval,
	DateTime, DisplayField, displayfield, Format, hr, mapfield, MaterialIcon, Notifier,
	tbar, Toolbar,
	Window
} from "@intermesh/goui";
import {client, JmapDataSource, jmapds, RecurrenceField} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";
import {CalendarEvent, CalendarItem} from "./CalendarItem.js";
import {calendarStore, statusIcons, t} from "./Index.js";


export class EventDetail extends Component {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: DataSourceForm

	item?: CalendarItem
	recurrenceId?: string

	store: JmapDataSource
	toolBar: Toolbar

	constructor() {
		super();
		this.title = t('Event');
		this.width = 440;
		//this.height = 620;

		this.store = jmapds("CalendarEvent");

		const recurrenceField = displayfield({
			hideWhenEmpty: false,
			name: 'recurrenceRule', flex: 1,
			renderer(this: DisplayField, v) {
				return RecurrenceField.toText(v, this.dataSet.start);
			}
		});
		this.toolBar = tbar({hidden:true, style:{alignItems:'space-between'}},
			btn({itemId: 'accepted', text:t('Accept'), handler:()=>this.updateStatus('accepted')}),
			btn({itemId: 'tentative', text:t('Maybe'), handler:()=>this.updateStatus('tentative')}),
			btn({itemId: 'declined', text:t('Decline'), handler:()=>this.updateStatus('declined')})
		);

		const alertField = alertfield({
			hidden:true,
			listeners:{
			'change': (me, newValue) => {
				if(this.item?.data.id) {
					if(newValue === null) {
						alertUseDefault.value = me.useDefault = false;
					} else {
						const isDefault = (newValue === 'default' || Object.keys(newValue).length === 0);
						me.useDefault = isDefault;
						alertUseDefault.value = isDefault;
					}
					this.form.value.useDefaultAlerts = newValue === 'default';
					this.form.submit(); // hoppakee
				}
			}
		}}),
		alertUseDefault = checkbox({hidden:true, name:'useDefaultAlerts', listeners: {
				'setvalue': (_, newV) => {
					if(newV) {alertField.useDefault = true;}
				}
			}})

		this.items.add(this.form = datasourceform({
				cls: 'scroll flow pad',
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
							this.toolBar.show();
							this.pressButton(this.item!.calendarPrincipal.participationStatus);
						}
					},
					'beforesave':(_, data) => {
						if(alertField.isModified()) {
							//@ts-ignore
							data.useDefaultAlerts = alertField.value === 'default'; // ?
						}
					}
				}
			},
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
			displayfield({name:'description'}),
			mapfield({name: 'participants',
				buildField: (v: any) => displayfield({
					//label: v.roles.owner?'Organizer': 'Participant',
					icon: statusIcons[v.participationStatus][0] as MaterialIcon,
					//icon: v.roles.owner ? 'manage_accounts' : (v.name?'person':'contact_mail'),
					renderer: (v) => {
						//const statusIcon = statusIcons[v.participationStatus] || v.participationStatus;
						let r = v.email;
						// type can be You or Organizer
						let type = '';
						if(v.email == this.item?.calendarPrincipal?.email) {
							type = this.item!.principalId === client.user.id ? t('You') : t('This');
						}
						if(v.roles.owner)
							type = t('Organizer');

						if(type) type = ' ('+type+')';

						if(v.name) {
							r = v.name + '<br>' + type;
						} else if(type) {
							r += '<br>' + type
						}
						return r; //+`<i class="icon" title="${statusIcon[1]}">${statusIcon[0]}</i>`;
					}
				})
			}),
			hr(),
			alertField, alertUseDefault,
			mapfield({name: 'links', cls:'goui-pit',
				buildField: (v: any) => containerfield({flex:'1 0 100%',cls: 'flow'},
					btn({icon: "description", text: v.title, flex:'1', style:{textAlign:'left'}, handler() {
						client.downloadBlobId(v.blobId, v.title).catch((error) => {
							Notifier.error(error);
						})
					}})
				)
			})
		),
			this.toolBar
		);
	}

	private pressButton(v:'accepted'|'declined'|'tentative') {
		this.toolBar.items.forEach(btn => {
			btn.el.cls('pressed', btn.itemId === v);
		});

	}
	private updateStatus(v:'accepted'|'declined'|'tentative') {
		this.item!.updateParticipation(v);
		this.pressButton(v);
	}


	loadEvent(ev: CalendarItem) {
		this.item = ev;
		if (!ev.key) {
			this.item = ev;
			this.form.create(ev.data);
		} else {
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
	}

}

export class EventDetailWindow extends Window {

	view: EventDetail
	constructor() {
		super();
		this.title = t('Event');
		this.width = 440;
		this.items.add(this.view = new EventDetail());
		//this.view.form.on('save', () => {this.close();})
	}

	loadEvent(ev: CalendarItem) {
		this.view.loadEvent(ev);
	}
}