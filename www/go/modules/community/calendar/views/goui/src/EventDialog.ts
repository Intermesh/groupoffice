
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

export class EventDialog extends Window {

	// title = t('New Event')
	// width = 800
	// height = 650
	form: Form

	constructor() {
		super();
		this.title= t('New Event');
		this.width = 800;
		this.height = 650;

		this.items.add(this.form = form({cls: 'scroll fit'},
			textfield({placeholder: t('Enter a title, name or place'), name: 'title' }),
			select({name:'calendarId', required:true, options: [
				{name:'key',value: 'value'}
			]}),
			fieldset({cls: 'c6'},
				checkbox({name: 'isAllDay', label: t('All day')}),
				datefield({label: t('Start'), name:'start'}),
				textfield({name: 'startTime'}),
				datefield({label:t('End'), name: 'end'}),
			),
			fieldset({cls: 'hbox'},
				select({name: 'freeBusyStatus', value: 'busy', required: true, options: [
					{name:'busy',value: t('Busy')},
					{name:'free',value: t('Free')}
				]}),
				select({name: 'privacy', required:true, value: 'standard', label: t('Visibility'), options: [
					{name:'standard',value:  t('Standard')},
					{name:'public', value: t('Public')},
					{name:'private',value:  t('Private')}
				]})
			),
			htmlfield({name:'description', label: t('Desciption')})
			),
			tbar({},
				btn({text:'Close', handler: _ => this.close()}),
				'->',
				btn({text:t('Save'), handler: _ => this.form.submit()})
			)
		);
	}

}