import {FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {checkbox, combobox, comp, t, textfield} from "@intermesh/goui";

export class CategoryDialog extends FormWindow {

	constructor() {
		super('CalendarCategory');
		this.title = 'category';
		this.width = 400;
		this.height = 300;

		this.on('beforerender', () => {
			this.title = t(this.currentId ? 'Edit category' : 'Create category');
		});
		const mayEditCategories = false; // todo: fetch from module permissions

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({name: 'name', label: t('Name'), flex:1, required:true}),
				combobox({
					label: t('Calendar'), name: 'calendarId',
					placeholder:t("All"), flex: '1 0',
					dataSource: jmapds("Calendar"),
					displayProperty: 'name',
				}),
				checkbox({hidden: !mayEditCategories, label: t('Global category')})
			)
		);
	}
}