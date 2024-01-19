import {
	btn, Button,
	comp, containerfield,
	DataSourceForm,
	datasourceform, DateInterval,
	DateTime, DisplayField, displayfield, Format, hr, mapfield, Notifier, numberfield, RecurrenceField,
	t,
	tbar, textfield, Toolbar,
	Window
} from "@intermesh/goui";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";
import {CalendarItem} from "./CalendarItem.js";


export class EventDetail extends Window {

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
		this.title = t('View Event');
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

						if(data.participants && this.item!.currentParticipant) {
							this.toolBar.show();
							this.pressButton(this.item!.currentParticipant.participationStatus);
						}
					},
					'save' : () => {this.close();}
				}
			},
			comp({cls:'hbox'},
				displayfield({label: t('Start'), name:'start',renderer:d=>Format.dateTime(d), flex:1}),
				displayfield({label:t('End'), name: 'end',renderer:d=>Format.dateTime(d), flex:1})
			),
			recurrenceField,
			displayfield({name: 'location', label:t('Location')}),
			displayfield({name:'description'}),
			mapfield({name: 'participants',
				buildField: (v: any) => displayfield({
					icon: (v.roles.owner ? 'person_3' : 'person'),
					renderer: v => v.name ? v.name + '<br>' + v.email : v.email
				})
			}),
			hr(),
			alertfield({listeners:{
				'change': (_, newValue) => {
					this.form.value.useDefaultAlert = newValue === 'default';
				}
			}}),
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


	load(ev: CalendarItem) {
		this.item = ev;
		this.title = t(!ev.key ? 'New event' : 'Edit event');
		if (!ev.key) {
			this.form.create(ev.data);
		} else {
			this.form.load(ev.data.id).then(() => {
				if(ev.recurrenceId) {
					this.form.findField('start')!.value = ev.start.format('Y-m-d\TH:i');
					this.form.findField('end')!.value = ev.end.format('Y-m-d\TH:i');
				}
			});
		}
	}

}