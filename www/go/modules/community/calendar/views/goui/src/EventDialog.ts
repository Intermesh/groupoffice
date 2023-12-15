import {
	browser,
	btn, Button,
	checkbox, CheckboxField,
	column,
	comp,
	DataSourceForm,
	datasourceform,
	DateField,
	datefield,
	DateTime,
	Format,
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
import {calendarStore} from "./Index.js";
import {participantfield} from "./ParticipantField.js";
import {alertfield} from "./AlertField.js";
import {CalendarItem} from "./CalendarItem.js";


export class EventDialog extends Window {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: DataSourceForm
	startTime: TextField
	endTime: TextField

	item?: CalendarItem
	recurrenceId?: string

	store: JmapDataSource
	submitBtn:Button
	endDate: DateField
	timeToggle: CheckboxField

	private titleField: TextField
	constructor() {
		super();
		this.title = t('New Event');
		this.width = 440;
		this.height = 820;
		this.store = jmapds("CalendarEvent");
		this.startTime = textfield({type:'time',value: '12:00', width: 120})
		this.endTime = textfield({type:'time',value: '13:00', width: 120})
		var recurrenceField = recurrencefield({name: 'recurrenceRule',flex:1});
		var alertField = alertfield();
		alertField.on('change', (_, newValue) => {
			this.form.value.useDefaultAlert = newValue === 'default';
		});

		const exceptionsBtn = btn({text:t('Exceptions'),width: 100, handler: b => {
			this.openExceptionsDialog();
		}});

		this.items.add(this.form = datasourceform({
				cls: 'scroll flow pad',
				flex:1,
				dataSource: this.store,
				listeners: {
					'beforesave': (frm,data) => {
						const end = frm.findField<DateField>('end')!.getValueAsDateTime()!,// DateTime.createFromFormat(data.end, 'Y-m-dTh:i'),
							start = frm.findField<DateField>('start')!.getValueAsDateTime()!;
						if(frm.value.showWithoutTime) {
							end.setHours(0,0,0).addDays(1);
							start.setHours(0,0,0);
							data.start = start.format('Y-m-d');// remove time
						}
						if(frm.isNew)
							data.timeZone = go.User.timezone; // enh: option to change in dialog?
						if(data.start || data.end)
							data.duration = start.diff(end);
						delete data.end;
					},
					'load': (_, data) => {
						const start = new DateTime(data.start);
						data.end = start.addDuration(data.duration).addDays(data.showWithoutTime? -1 : 0).format(data.showWithoutTime ? 'Y-m-d' : 'c');
						exceptionsBtn.hidden = !data.recurrenceOverrides;
						//recurrenceField.setStartDate(start)
					},
					'save' : () => {this.close();}
				}
			},
			this.titleField = textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 70%' }),
			select({
				label: t('Calendar'), name: 'calendarId', required: true, flex: '1 20%',
				store: calendarStore, valueField: 'id',
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
			textfield({name: 'location', label:t('Location')}),
			this.timeToggle = checkbox({type:'switch',name: 'showWithoutTime', label: t('All day'), listeners: {'setvalue':(_,checked) => {
				alertField.fullDay = checked;
				alertField.drawOptions();
				calendarStore.dataSource.single(this.form.value.calendarId).then(r => {
					if(!r) return;
					const d = checked ? r.defaultAlertsWithoutTime : r.defaultAlertsWithTime;
					alertField.setDefaultLabel(d)
				});
				this.startTime.hidden = this.endTime.hidden = checked;
			}}}),
			comp({cls:'hbox'},
				datefield({label: t('Start'), name:'start', flex:1, timeField: this.startTime, listeners:{'setvalue': (me,v) => {
					const time = !this.timeToggle.value;
					const start = DateTime.createFromFormat(v,'Y-m-d'+(time ? "TH:i":''));

					if(start){
						// if(this.endDate.changed) {
						// 	this.endDate.value = start.addDuration(this.item!.data.duration || 'P1H').format(this.outputFormat+'TH:i');
						// }
						this.endDate.minDate = start;
					}
					const date = DateTime.createFromFormat(v,me.inputFormat);
					if(date)
						recurrenceField.setStartDate(date);
				}}}),
				this.startTime
			),
			comp({cls:'hbox'},
				this.endDate = datefield({label:t('End'), name: 'end', flex:1, timeField: this.endTime}),
				this.endTime
			),
			comp({cls:'hbox'},
				recurrenceField,
				exceptionsBtn,
			),
			participantfield({
				listeners: {'change': (_,v) => {
					this.submitBtn.text = t((v && Object.keys(v).length) ? 'Send' : 'Save');

					console.log(v);
				}}
			}),
			alertField,
			textarea({name:'description', label: t('Description'), autoHeight: true}),

			radio({type:'button',value: 'busy', name: 'freeBusyStatus', flex:'1 40%',options: [
				{value:'busy',text: t('Busy')},
				{value:'free',text: t('Free')}
			]}),
			select({name: 'privacy', flex:'1 40%', required:true, value: 'public', label: t('Visibility'), options: [
				{value:'public', name: t('Public')},
				{value:'private',name:  t('Private')},
				{value:'secret',name:  t('Secret')}
			]}),

		),
		tbar({},
			btn({icon:'attach_file', handler: _ => this.attachFile() }),
			'->',
			this.submitBtn = btn({text:t('Save'), handler: _ => this.submit()})
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
		this.titleField.focus();
		this.titleField.input!.select();
	}

	submit() {
		if(this.item!.isRecurring) {
			this.item!.patch(this.form.modified, v => {
				this.close();
				return v;
			});
		} else {
			this.item!.confirmScheduleMessage(this.form.modified, () => {
				this.form.submit();
			});
		}
	}

	private async attachFile() {
		 const files = await browser.pickLocalFiles(true);
		 this.mask();
		 const blobs = await client.uploadMultiple(files);
		 this.unmask();
		 console.warn(blobs);
	}

}