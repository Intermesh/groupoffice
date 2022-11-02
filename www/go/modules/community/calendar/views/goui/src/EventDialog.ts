
import {form, Form} from "@goui/component/form/Form.js";
import {fieldset} from "@goui/component/form/Fieldset.js";
import {textfield} from "@goui/component/form/TextField.js";
import {checkbox} from "@goui/component/form/CheckboxField.js";
import {datefield} from "@goui/component/form/DateField.js";
import {select} from "@goui/component/form/SelectField.js";
import {tbar} from "@goui/component/Toolbar.js";
import {t} from "@goui/Translate.js";
import {htmlfield} from "@goui/component/form/HtmlField.js";
import {btn} from "@goui/component/Button.js";
import {Window} from "@goui/component/Window.js";
import {comp} from "@goui/component/Component.js";
import {TextField} from "@goui/component/form/TextField.js";
import {client} from "@goui/jmap/Client.js";
import {DateTime} from "@goui/util/DateTime.js";

export class EventDialog extends Window {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: Form
	startTime: TextField
	endTime: TextField

	constructor() {
		super();
		this.title = t('New Event');
		this.width = 600;
		this.height = 550;
		this.startTime = textfield({value: '12:00', width: 80})
		this.endTime = textfield({value: '13:00', width: 80})

		this.items.add(this.form = form({
				cls: 'scroll flow pad',
				flex:1,
				store: client.store('CalendarEvent'),
				listeners: {'serialize': (_,data) => {
					data.duration = (new DateTime(data.start)).diff(new DateTime(data.end));
					delete data.end;
				}}
			},
			textfield({placeholder: t('Enter a title, name or place'), name: 'title', flex: '0 1 70%' }),
			select({name:'calendarId', required:true, flex: '1 20%', options: [
				{value:'2',name: 'Michael'}
			]}),
			comp({flex: '1 40%'},
				checkbox({type:'switch',name: 'showWithoutTime', label: t('All day'), listeners: {'change':(_,checked) => {
					this.startTime.hidden = this.endTime.hidden = checked;
				} }}),
				comp({cls:'hbox'},
					datefield({label: t('From'), name:'start', flex:1, timefield: this.startTime}),
					this.startTime
				),
				comp({cls:'hbox'},
					datefield({label:t('To'), name: 'end', flex:1, timefield: this.endTime}),
					this.endTime
				)
				// new go.form.RecurrenceField({
				// 	name: 'recurrenceRule'
				// })
				// select({name: 'recurrenceRule', value: '-', required: true, options: [
				// 	{value:'-',name: t('Niet herhaald')},
				// ]})
			),
			comp({flex: '1 40%', cls:'flow'},
				textfield({name: 'location', label:t('Location')}),
				textfield({label:t('Invite people')}),
				select({name: 'freeBusyStatus', flex:'1 40%', value: 'busy', label: t('Availability'), required: true, options: [
					{value:'busy',name: t('Busy')},
					{value:'free',name: t('Free')}
				]}),
				select({name: 'privacy', flex:'1 40%', required:true, value: 'public', label: t('Visibility'), options: [
					{value:'public', name: t('Public')},
					{value:'private',name:  t('Private')},
					{value:'secret',name:  t('Secret')}
				]})
			),
			htmlfield({name:'description', label: t('Description')})
			),
			tbar({},
				'->',
				btn({text:t('Save'), handler: _ => this.form.submit()})
			)
		);
	}

}