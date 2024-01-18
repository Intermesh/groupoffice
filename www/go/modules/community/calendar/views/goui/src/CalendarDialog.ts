import {
	btn,
	colorfield, column, ComboBox, combobox,
	comp, datasourcestore,
	hr,
	radio,
	t, table,
	textfield,
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {alertfield} from "./AlertField.js";

export class CalendarDialog extends FormWindow {


	constructor() {
		super('Calendar');
		this.title = 'calendar';
		this.width = 460;
		this.height = 650;

		// const unsubscribeBtn = btn({text:t('Unsubscribe'), handler:()=> {
     //    this.form.dataSource.update(this.currentId!, {isSubscribed:false});
     //    this.close();
     // }});

		this.on('beforerender', () => {
			this.title = t(this.currentId ? 'Edit calendar' : 'Create calendar');
		})

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({name: 'name', label: t('Name'), flex:1}),
				colorfield({name: 'color', label: t('Color'), width: 100}),
				textfield({name:'description', label: t('Description')}),
				// radio({style:{'width':'auto'}, type:'button',itemId:'type', value: 'personal', options: [
				// 	{text:t('Personal'), value: 'personal'},
				// 	{text:t('Shared'), value: 'shared'}
				// ]}),
				combobox({
					dataSource: jmapds("Principal"), displayProperty: 'name', filter: {entity: 'User'},
					label: t("Owner"), name: "ownerId", filterName: "text", flex:'1 0'
				}),
				hr(),
				comp({tagName:'h3',flex:'1 0 100%',text:t('Default notifications') }),
				alertfield({name: 'defaultAlertsWithTime',isForDefault:true, label:t('Events (with time)')}),
				alertfield({name: 'defaultAlertsWithoutTime',isForDefault:true, fullDay:true, label:t('Full-day events (without time)')})
				//unsubscribeBtn
			),
		);

		// this.form.on('load', (me, data) => {
		// 	unsubscribeBtn.hidden = !data.id;
		// })
		//
		// this.cards.items.add(comp({title: t('Categories')},
		// 	table({
		// 		columns: [
		// 			column({id: 'name', header: t('Name')})
		// 		],store:datasourcestore({dataSource:jmapds('CalendarCategory')})
		// 	})));

		this.addCustomFields();

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