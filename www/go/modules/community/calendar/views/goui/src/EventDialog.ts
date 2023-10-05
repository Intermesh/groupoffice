
import {DataSourceForm, datasourceform, DateField, form, Form} from "@intermesh/goui";
import {fieldset} from "@intermesh/goui";
import {textfield} from "@intermesh/goui";
import {checkbox} from "@intermesh/goui";
import {datefield} from "@intermesh/goui";
import {select} from "@intermesh/goui";
import {tbar} from "@intermesh/goui";
import {t} from "@intermesh/goui";
import {htmlfield} from "@intermesh/goui";
import {btn} from "@intermesh/goui";
import {win, Window} from "@intermesh/goui";
import {comp} from "@intermesh/goui";
import {TextField} from "@intermesh/goui";
import {client, JmapDataSource, jmapds} from "@intermesh/groupoffice-core";
import {DateTime} from "@intermesh/goui";
import {radio} from "@intermesh/goui";
import {textarea} from "@intermesh/goui";
import {calendarStore} from "./Index.js";
import {ParticipantField} from "./ParticipantField.js";
import {containerfield} from "@intermesh/goui";
import {mapfield} from "@intermesh/goui";
import {recurrencefield} from "@intermesh/goui";
import {AlertField} from "./AlertField.js";
import {CalendarEvent, CalendarItem} from "./CalendarView.js";


export class EventDialog extends Window {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: DataSourceForm
	startTime: TextField
	endTime: TextField

	recurrenceId?: string

	store: JmapDataSource

	constructor() {
		super();
		this.title = t('New Event');
		this.width = 540;
		this.height = 550;
		this.store = jmapds("CalendarEvent");
		this.startTime = textfield({value: '12:00', width: 80})
		this.endTime = textfield({value: '13:00', width: 80})
		var recurrenceField = recurrencefield({name: 'recurrenceRule', label: t('Recurrence')});

		this.items.add(this.form = datasourceform({
				cls: 'scroll flow pad',
				flex:1,
				dataSource: this.store,
				listeners: {
					'beforesave': (frm,data) => {
						const end = frm.findField<DateField>('end')!.date!,// DateTime.createFromFormat(data.end, 'Y-m-dTh:i'),
							start = frm.findField<DateField>('start')!.date!;
						if(data.showWithoutTime) {
							end.setHours(0,0,0).addDays(1);
							start.setHours(0,0,0);
							data.start = start.format('Y-m-d');// remove time
						}
						data.timeZone = go.User.timezone; // todo: option to change in dialog
						data.duration = start.diff(end);
						delete data.end;
					},
					'load': (_, data) => {
						const start = new DateTime(data.start);
						data.end = start.addDuration(data.duration).addDays(data.showWithoutTime? -1 : 0).format('c');
						//recurrenceField.setStartDate(start)
					},
					'save' : () => {this.close();}
				}
			},
			textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 70%' }),
			select({name:'calendarId', required:true, flex: '1 20%', store: calendarStore, valueField:'id', textRenderer:(r:any)=>r.name }),
			comp({flex: '1 40%'},
				checkbox({type:'switch',name: 'showWithoutTime', label: t('All day'), listeners: {'setvalue':(_,checked) => {
					this.startTime.hidden = this.endTime.hidden = checked;
				} }}),
				comp({cls:'hbox'},
					datefield({label: t('From'), name:'start', flex:1, timefield: this.startTime, listeners:{'setvalue': (me,v) => {
						const date = DateTime.createFromFormat(v,me.inputFormat);
						if(date)
							recurrenceField.setStartDate(date);
					}}}),
					this.startTime
				),
				comp({cls:'hbox'},
					datefield({label:t('To'), name: 'end', flex:1, timefield: this.endTime}),
					this.endTime
				),
				recurrenceField,
			),
			comp({flex: '1 40%', cls:'flow'},
				textfield({name: 'location', label:t('Location')}),
				new ParticipantField(),
				radio({type:'button',value: 'busy', name: 'freeBusyStatus', flex:'1 40%',options: [
					{value:'busy',text: t('Busy')},
					{value:'free',text: t('Free')}
				]}),
				select({name: 'privacy', flex:'1 40%', required:true, value: 'public', label: t('Visibility'), options: [
					{value:'public', name: t('Public')},
					{value:'private',name:  t('Private')},
					{value:'secret',name:  t('Secret')}
				]})
			),
			textarea({name:'description', label: t('Description')}),
			new AlertField()
		),
		tbar({},
			'->',
			btn({text:t('Save'), handler: _ => this.submit()})
		));
	}

	load(ev: CalendarItem) {
		delete this.recurrenceId;
		if (!ev.data.id) {
			this.form.create(ev.data);
		} else {
			if (ev.recurrenceId)
				this.recurrenceId = ev.recurrenceId;
			this.form.load(ev.data.id);
		}
	}

	submit() {
		if(!this.recurrenceId) {
			this.form.submit();
		} else {
			const thisSeriesFutureDialog = win({
					title: t('Edit recurring event')
				},comp({
					cls:'pad',
					html: t('Which event would you like to edit?')
				}),tbar({},btn({
						text: t('This event'),
						handler: b => this.patchOccurrence(this.form.value as CalendarEvent, this.recurrenceId!)
					}),btn({
						text: t('This and future'),
						handler: b => this.patchThisAndFuture({} as CalendarItem)
					}),btn({
						text: t('All events'), // save to series
						handler: b => this.form.submit()
					})
				)
			);
			thisSeriesFutureDialog.show();
		}
	}



	private patchOccurrence(data: CalendarEvent, recurrenceId: string) {
		this.store.update({
			id: data.id!,
			recurrenceOverrides: {[recurrenceId]: data}
		});
	}

	/**
	 * @see  https://www.ietf.org/archive/id/draft-ietf-jmap-calendars-10.html#section-5.5
	 * @param ev CalendarItem data the event item created by the DnD view
	 */
	private patchThisAndFuture(ev:CalendarItem) {
		//if(ev.data)
		alert('todo');
	}

}