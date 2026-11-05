import {client, FormWindow, jmapds} from "@intermesh/groupoffice-core";
import {checkbox, colorfield, combobox, comp, hiddenfield, textfield} from "@intermesh/goui";
import {t} from "./Index.js";

export class CategoryWindow extends FormWindow {
	private calendarIdFld;

	constructor() {
		super('CalendarCategory');
		this.title = 'category';
		this.width = 380;
		this.height = 390;

		this.on('beforerender', () => {
			this.title = t(this.form.currentId ? 'Edit category' : 'Create category');
		});

		this.form.on('beforesave', ( {data}) => {
			if(data.isGlobal) {
				data.ownerId = null;
				data.calendarId = null;
			}
			delete data.isGlobal;
			return data;
		});
		this.form.on('beforeload', ( {data}) => {
			data.isGlobal = data.ownerId === null;
		});

		this.generalTab.items.add(
			comp({cls:'flow pad'},
				textfield({
					name: 'name',
					label: t('Name'),
					required:true,
					flex:1
				}),

				colorfield({
					name: 'color',
					label: t('Color'),
					width: 100
				}),

				this.calendarIdFld = combobox({
					label: t('Calendar'),
					name: 'calendarId',
					placeholder:t("All"),
					clearable: true,
					dataSource: jmapds("Calendar"),
					displayProperty: 'name',
					storeConfig: {
						filters: {manageOnly: {permissionLevel: go.permissionLevels.manage}}
					}
				}),

				checkbox({
					hidden: !client.user.isAdmin,
					name: 'isGlobal',
					label: t('Global category'),
					listeners: {
						setvalue: ({newValue}) => {
							this.calendarIdFld.disabled = newValue;
						}
					}
				})
			)
		);

	}
}