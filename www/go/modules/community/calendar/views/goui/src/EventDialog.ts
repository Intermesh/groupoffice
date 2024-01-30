import {
	autocompletechips,
	browser,
	btn, Button,
	checkbox, CheckboxField, checkboxselectcolumn, chips,
	column,
	comp, ContainerField, containerfield,
	DataSourceForm,
	datasourceform, datasourcestore,
	DateField,
	datefield, DateInterval,
	DateTime, displayfield,
	Format, hr, MapField, mapfield, menu, Notifier, numberfield,
	radio,
	recurrencefield,
	select,
	store,
	t,
	table,
	tbar,
	textarea,
	textfield,
	TextField,
	win,
	Window
} from "@intermesh/goui";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {calendarStore, categoryStore} from "./Index.js";
import {ParticipantField, participantfield} from "./ParticipantField.js";
import {alertfield} from "./AlertField.js";
import {CalendarItem} from "./CalendarItem.js";
import {AvailabilityDialog} from "./AvailabilityDialog.js";


export class EventDialog extends Window {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: DataSourceForm
	// startTime: TextField
	// endTime: TextField

	item?: CalendarItem
	recurrenceId?: string

	store: JmapDataSource
	submitBtn:Button
	endDate: DateField
	startDate: DateField
	withoutTimeToggle: CheckboxField

	attachments:MapField
	btnFreeBusy: Button

	private titleField: TextField
	constructor() {
		super();
		this.title = t('New Event');
		this.width = 440;
		this.height = 820;
		this.store = jmapds("CalendarEvent");
		// this.startTime = textfield({type:'time',value: '12:00', width: 128})
		// this.endTime = textfield({type:'time',value: '13:00', width: 128})
		var recurrenceField = recurrencefield({name: 'recurrenceRule',flex:1});
		var alertField = alertfield();
		alertField.on('change', (_, newValue) => {
			this.form.value.useDefaultAlert = newValue === 'default';
		});

		const exceptionsBtn = btn({text:t('Exceptions'),width: 100, handler: b => {
			this.openExceptionsDialog();
		}});

		const now = new DateTime();

		this.items.add(this.form = datasourceform({
				cls: 'scroll flow pad',
				flex:1,
				dataSource: this.store,
				listeners: {
					'beforesave': (frm,data) => {
						this.parseSavedData(data);
					},
					'load': (_, data) => {
						const start = new DateTime(data.start);
						data.end = start.add(new DateInterval(data.duration))
							.addDays(data.showWithoutTime? -1 : 0)
							.format(data.showWithoutTime ? 'Y-m-d' : 'Y-m-dTH:i:s');
						exceptionsBtn.hidden = !data.recurrenceOverrides;
						//recurrenceField.setStartDate(start)
					},
					'save' : () => {this.close();}
				}
			},
			this.titleField = textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 60%'}),
			select({
				label: t('Calendar'), name: 'calendarId', required: true, flex: '1 30%',
				store: calendarStore,
				valueField: 'id',
				textRenderer: (r: any) => r.name,
				listeners: {
					'setvalue': (me, v) => {
						if(v)
						calendarStore.dataSource.single(v).then(r => {
							if(!r) return;
							const d = this.form.value.showWithoutTime ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
								alertField.setDefaultLabel(d)
						});

					}
				}
			}),
			textfield({name: 'location',flex:1, label:t('Location')}),
			btn({icon:'video_call', cls:'filled', width:50, handler(btn) { (btn.previousSibling() as TextField)!.value = 'link'}}),
			this.withoutTimeToggle = checkbox({type:'switch',name: 'showWithoutTime', label: t('All day'),
				listeners: {'setvalue':(_, checked) => {
					alertField.fullDay = checked;
					alertField.drawOptions();
					calendarStore.dataSource.single(this.form.value.calendarId).then(r => {
						if(!r) return;
						const d = checked ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
						alertField.setDefaultLabel(d)
					});
					this.startDate.withTime = this.endDate.withTime = !checked;
				}}
			}),
			this.startDate = datefield({label: t('Start'), name:'start', flex:1, defaultTime: now.format('H')+':00',
				listeners:{'setvalue': (me,v) => {
					const date = me.getValueAsDateTime();
					if(date){
						// if(this.endDate.changed) {
						// 	this.endDate.value = start.addDuration(this.item!.data.duration || 'P1H').format(this.outputFormat+'TH:i');
						// }
						recurrenceField.setStartDate(date);
						this.endDate.min = date.format('Y-m-d H:i');
					}
				}}
			}),
			this.endDate = datefield({label:t('End'), name: 'end', flex:1, defaultTime: (now.getHours()+1 )+':00'}),
			comp({cls:'hbox'},
				recurrenceField,
				exceptionsBtn,
			),
			participantfield({
				listeners: {'change': (_,v) => {
					const count = (v && Object.keys(v).length);
					this.submitBtn.text = t(count ? 'Send' : 'Save');
					this.btnFreeBusy.hidden = !count;

				}}
			}),
			this.btnFreeBusy = btn({hidden: true,text:t('Check availability'), handler: () => {
				const dlg = new AvailabilityDialog();
				dlg.on('changetime', (_,s,e) => {
					this.startDate.value = s.format('Y-m-dTH:i');
					this.endDate.value = e.format('Y-m-dTH:i');
				});
				dlg.show(this.item, this.form.modified);
			} }),
			alertField,
			textarea({name:'description', label: t('Description'), autoHeight: true}),
			autocompletechips({
				list: table({fitParent: true, headers: false, store: datasourcestore({dataSource:categoryStore.dataSource}),
					rowSelectionConfig: {multiSelect: true},
					columns: [
						checkboxselectcolumn(),
						column({id: "name"})
					]
				}),
				label: "Categories",
				name: "categoryIds",
				chipRenderer: async (chip, id) => {
					categoryStore.dataSource.single(id).then(v => { chip.text = v?.name ?? '???'});
				},
				listeners: {
					autocomplete: (field, input) => {
						field.list.store.queryParams = {filter: {name: input}};
						field.list.store.load();
					}
				}
			}),
			radio({type:'button',value: 'busy', name: 'freeBusyStatus', flex:'1 40%',options: [
				{value:'busy',text: t('Busy')},
				{value:'free',text: t('Free')}
			]}),
			select({name: 'privacy', flex:'1 40%', required:true, value: 'public', label: t('Visibility'), options: [
				{value:'public', name: t('Public')},
				{value:'private',name:  t('Private')},
				{value:'secret',name:  t('Secret')}
			]}),
			this.attachments = mapfield({name: 'links', cls:'goui-pit',
				buildField: (v: any) => {
					// const userIcon = v.name?'person':'email',
					// 	statusIcon = ParticipantField.statusIcons[v.participationStatus] || v.participationStatus;
					return containerfield({flex:'1 0 100%',cls: 'flow'},
						textfield({hidden:true,name:'title'}),
						numberfield({hidden:true,name:'size'}),
						textfield({hidden:true,name:'contentType'}),
						textfield({hidden:true,name:'blobId'}),
						btn({icon: "description", text: v.title, flex:'1', style:{textAlign:'left'}, handler() {
							client.downloadBlobId(v.blobId, v.title).catch((error) => {
								Notifier.error(error);
							})
						}}),
						btn({icon: "delete", width:50, handler(btn) {btn.parent!.remove();}})
					);
				}
			})
		),
		tbar({cls: 'border-top'},
			btn({icon:'attach_file', handler: _ => this.attachFile() }),
			'->',
			this.submitBtn = btn({text:t('Save'), cls:'primary',handler: _ => this.submit()})
		));

	}

	private openExceptionsDialog() {
		const o = this.form.value.recurrenceOverrides;
		let d = [];
		for(let recurrenceId in o) {
			d.push({recurrenceId, excluded: o[recurrenceId]?.excluded})
		}
		const exceptionStore = store({items:d}),
			exceptionView = win({
				title: t('Exceptions'),
				height:400,
			}, table({
				fitParent:true,
				store: exceptionStore,
				columns: [
					column({id: "recurrenceId", header:t('Start'), renderer(v,record) {
						return Format.dateTime(v)+ `<br><small>${record.excluded ? 'Excluded' : 'Override'}</small>`;
					}}),
					column({id: "excluded", header: '', width:90, renderer: (v,r) => btn({icon:'delete',handler:()=>{
						this.item!.undoException(r.recurrenceId).then(_=> { exceptionStore.remove(r)})
					}})
					}),
				]
			}));

		exceptionView.show();
	}

	parseSavedData(data: any) {
		const end = this.endDate.getValueAsDateTime()!,// DateTime.createFromFormat(data.end, 'Y-m-dTh:i'),
			start = this.startDate.getValueAsDateTime()!;
		if(this.form.value.showWithoutTime) {
			end.setHours(0,0,0).addDays(1);
			start.setHours(0,0,0);
			data.start = start.format('Y-m-d');// remove time
		}
		if(this.form.isNew)
			data.timeZone = go.User.timezone; // enh: option to change in dialog?
		if(data.start || data.end)
			data.duration = start.diff(end).toIso8601();
		delete data.end;
		return data;
	}

	load(ev: CalendarItem) {
		this.item = ev;
		this.title = t(!ev.key ? 'New event' : 'Edit event');
		if (!ev.key) {
			this.form.create(ev.data);
		} else {
			this.form.load(ev.data.id).then(() => {
				if(ev.recurrenceId) {
					this.startDate.value = ev.start.format('Y-m-d\TH:i');
					this.endDate.value = ev.end.clone().addDays(ev.data.showWithoutTime? -1 : 0).format(ev.data.showWithoutTime ? 'Y-m-d' : 'c');
				}
			});
		}
		this.titleField.focus();
		this.titleField.input!.select();
	}

	submit() {
		if(this.item!.isRecurring) {
			this.item!.patch(this.parseSavedData(this.form.modified), v => {
				this.close();
				return v;
			});
		} else {
			this.item!.confirmScheduleMessage(this.parseSavedData(this.form.modified), () => {
				this.form.submit();
			});
		}
	}

	private async attachFile() {
		 const files = await browser.pickLocalFiles(true);
		 this.mask();
		 const blobs = await client.uploadMultiple(files);
		 this.unmask();
		 for(const r of blobs)
		 	this.attachments.add({blobId:r.id, title:r.name, size:r.size, contentType:r.type}, );
		 //console.warn(blobs);
	}

}