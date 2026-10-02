import {FormWindow, principalDS} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";
import {colorfield, combobox, hiddenfield, select, textarea, textfield} from "@intermesh/goui";
import {resourceGroupStore} from "./ResourcesWindow.js";

export class ResourceWindow extends FormWindow {
	constructor() {
		super('Calendar');
		this.title = t('Resource');
		this.maximizable = false;

		this.generalTab.cls = 'flow pad';
		this.generalTab.items.add(
			select({
				name: 'groupId',
				required: true,
				label: t('Group'),
				store: resourceGroupStore,
				valueField: 'id',
				textRenderer: (r: any) => r.name
			}),

			textfield({name: 'name', flex: 1, label: t('Name')}),
			colorfield({name: 'color', width: 100, value: '69554f'}),
			textarea({name: 'description', label: t('Description')}),
			hiddenfield({name: 'includeInAvailability', value: 'all'})
			//checkbox({disabled:true, name:'needsApproval', label: t('Needs approval')})
		);


		this.on('ready', async ({currentId}) => {
			await resourceGroupStore.load();
		});

		this.addCustomFields();

		this.addSharePanel();
	}
}