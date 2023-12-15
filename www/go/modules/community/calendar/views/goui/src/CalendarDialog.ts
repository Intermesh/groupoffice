import {
	btn,
	colorfield, ComboBox, combobox,
	comp,
	hr,
	radio,
	t,
	textfield,
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";

export class CalendarDialog extends FormWindow {


	constructor() {
		super('Calendar');
		this.title = t('Edit calendar');
		this.width = 460;
		this.height = 650;

		// const unsubscribeBtn = btn({text:t('Unsubscribe'), handler:()=> {
     //    this.form.dataSource.update(this.currentId!, {isSubscribed:false});
     //    this.close();
     // }});

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({name: 'name', label: t('Name'), flex:1}),
				colorfield({name: 'color', label: t('Color'), width: 100}),
				textfield({name:'description', label: t('Description')}),
				radio({style:{'width':'auto'}, type:'button',itemId:'type', value: 'personal', options: [
					{text:t('Personal'), value: 'personal'},
					{text:t('Shared'), value: 'shared'}
				]}),
				combobox({
					dataSource: jmapds("User"), displayProperty: 'displayName',
					label: t("Owner"), name: "ownerId", filterName: "text", flex:'1 0'
				}),
				//selectuser({name: 'ownerId', flex:'1 0', label: t('Owner')}),
				hr(),
				comp({tagName:'h3',flex:'1 0 100%',text:t('Default notifications') }),
				alertfield({name: 'defaultAlertsWithTime',isForDefault:true, label:t('Events (with time)')}),
				alertfield({name: 'defaultAlertsWithoutTime',isForDefault:true, fullDay:true, label:t('Full-day events (without time)')})
			// containerfield({flex:'1 0 100%',cls: 'flow',itemId:'defaultAlertsWithTime'},
			// 	select({width: 120, name:'action', options:[
			// 		{name: t('Email'), value:'email'},
			// 		{name:t('Notification'), value: 'display'}
			// 	]}),
			// 	numberfield({width: 70, name:'offset', decimals:0, value:1}),
			// 	select({flex:'1 0', options: [
			// 			{value: '0', name: t('at start time')},
			// 			{value: 'minutes', name: t('minute(s) before')},
			// 			{value: 'hours', name: t('hour(s) before')},
			// 			{value: 'days', name: t('day(s) before')},
			// 	]})
			// ),

			// comp({flex:'1 0 100%',text:t('Event notifications for all-day event') }),
			// containerfield({flex:'1 0 100%',cls: 'flow',itemId:'defaultAlertsWithoutTime'},
			// 	select({width: 120, name:'action', options:[
			// 			{name:t('Notification'), value: 'display'},
			// 		{name: t('Email'), value:'email'}
			// 	]}),
			// 	numberfield({width: 70, name:'offset', decimals:0, value:1}),
			// 	select({flex:'1 0',options: [
			// 		{value: 'sameday', name: t('at the same day')},
			// 		{value: 'daysbefore', name: t('day(s) before')},
			// 	]}),
			// 	comp({width: 15,'text': 'at'}),
			// 	textfield({width: 80, name:'time', value: '09:00'})
			// )
				//unsubscribeBtn
			),
		);

		// this.form.on('load', (me, data) => {
		// 	unsubscribeBtn.hidden = !data.id;
		// })

		this.addSharePanel([
			{value: "",name: ""},
			{value: 5, name: t("Read free/busy")},
			{value: 10,name: t("Read items")},
			{value: 20,name: t("Update private")},
			{value: 25,name: t("RSVP")},
			{value: 30,name: t("Write own")},
			{value: 35,name: t("Write all")},
			{value: 50,name: t("Manage")}
		]);
		// tbar({},
		// 	'->',
		// 	btn({text:t('Save'), handler: _ => this.form.submit()})
		// ));
	}
}