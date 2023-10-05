import {
	colorfield,
	comp,
	containerfield,
	numberfield,
	radio,
	select,
	t,
	textfield,
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class CalendarDialog extends FormWindow {


	constructor() {
		super('Calendar');
		this.title = t('Edit calendar');
		this.width = 520;
		this.height = 550;

		this.generalTab.items.add(
			comp({cls:'flow', flex:'1 0 100%'},
				textfield({name: 'name', label: t('Name'), flex:'1 0'}),
				colorfield({name: 'color', label: t('Color'), width: 120}),
			),
			comp({cls:'flow', flex:'1 0 100%'},
				radio({style:{'width':'auto'}, type:'button',itemId:'type', value: 'personal', options: [
					{text:t('Personal'), value: 'personal'},
					{text:t('Shared'), value: 'shared'}
				]}),
				textfield({name: 'ownerId', flex:'1 0', label: t('Owner')}),
			),
			textfield({name:'description', label: t('Description')}),
			comp({flex:'1 0 100%',text:t('Event notifications') }),
			containerfield({flex:'1 0 100%',cls: 'flow',itemId:'defaultAlertsWithTime'},
				select({width: 120, name:'action', options:[
					{name: t('Email'), value:'email'},
					{name:t('Notification'), value: 'display'}
				]}),
				numberfield({width: 70, name:'offset', decimals:0, value:1}),
				select({flex:'1 0', options: [
						{value: '0', name: t('at start time')},
						{value: 'minutes', name: t('minute(s) before')},
						{value: 'hours', name: t('hour(s) before')},
						{value: 'days', name: t('day(s) before')},
				]})
			),

			comp({flex:'1 0 100%',text:t('Event notifications for all-day event') }),
			containerfield({flex:'1 0 100%',cls: 'flow',itemId:'defaultAlertsWithoutTime'},
				select({width: 120, name:'action', options:[
						{name:t('Notification'), value: 'display'},
					{name: t('Email'), value:'email'}
				]}),
				numberfield({width: 70, name:'offset', decimals:0, value:1}),
				select({flex:'1 0',options: [
					{value: 'sameday', name: t('at the same day')},
					{value: 'daysbefore', name: t('day(s) before')},
				]}),
				comp({width: 15,'text': 'at'}),
				textfield({width: 80, name:'time', value: '09:00'})
			)
		);

		this.addSharePanel();
		// tbar({},
		// 	'->',
		// 	btn({text:t('Save'), handler: _ => this.form.submit()})
		// ));
	}
}