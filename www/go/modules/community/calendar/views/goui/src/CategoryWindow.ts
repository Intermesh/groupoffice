import {client, FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {checkbox, colorfield, combobox, comp, t, textfield} from "@intermesh/goui";

export class CategoryWindow extends FormWindow {

	constructor() {
		super('CalendarCategory');
		this.title = 'category';
		this.width = 380;
		this.height = 360;

		this.on('beforerender', () => {
			this.title = t(this.currentId ? 'Edit category' : 'Create category');
		});

		//client.getUser().capabilities['community:calendar'].mayEditCategories;
		const mayEditCategories = false; // todo: fetch from module permissions

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({name: 'name', label: t('Name'), required:true, flex:1}),
				colorfield({name: 'color', label: t('Color'), width: 100}),
				combobox({
					label: t('Calendar'), name: 'calendarId',
					placeholder:t("All"),
					dataSource: jmapds("Calendar"),
					displayProperty: 'name',
				}),
				checkbox({hidden: !mayEditCategories, label: t('Global category')})
			)
		);
	}
}