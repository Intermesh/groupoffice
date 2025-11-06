import {client, FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {checkbox, colorfield, combobox, comp, hiddenfield, textfield} from "@intermesh/goui";
import {t} from "./Index.js";

export class CategoryWindow extends FormWindow {

	constructor() {
		super('CalendarCategory');
		this.title = 'category';
		this.width = 380;
		this.height = 390;

		this.on('beforerender', () => {
			this.title = t(this.form.currentId ? 'Edit category' : 'Create category');
		});

		this.form.on('beforesave', ( {data}) => {
			data.ownerId = data.isGlobal ? null : client.user.id;
			delete data.isGlobal;
			return data;
		});
		this.form.on('load', ( {data}) => {
			data.isGlobal = data.ownerId === null;
		});

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({name: 'name', label: t('Name'), required:true, flex:1}),
				colorfield({name: 'color', label: t('Color'), width: 100}),
				combobox({
					label: t('Calendar'), name: 'calendarId',
					placeholder:t("All"),
					dataSource: jmapds("Calendar"),
					displayProperty: 'name',
					storeConfig: {
						filters: {manageOnly: {permissionLevel: go.permissionLevels.manage}}
					}
				}),
				checkbox({hidden: !client.user.isAdmin,name:'isGlobal', label: t('Global category')})
			)
		);

	}
}