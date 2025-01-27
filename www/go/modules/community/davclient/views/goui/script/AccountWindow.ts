import {
	checkbox,
	comp, select,
	textfield, t
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";
export class AccountWindow extends FormWindow {

	constructor() {
		super('DavAccount');
		this.title = t('Account');
		this.generalTab.items.add(comp({cls:'flow pad'},
				//checkbox({type:'switch',name:'active',value:true,label:t('Enabled')}),
				textfield({name:'name', label: t('Name')}),
				textfield({name:'host', label: t('Host')}),
				// textfield({name:'principalUri', label: t('Path')}),
				textfield({name:'username', label: t('Username')}),
				textfield({name:'password', label: t('Password'), type:'password'}),
				//textfield({name:'uri', readOnly:true, label: t('Common name')}),
				select({name:'refreshInterval', label: t('Refresh calendars'), value:15,options: [
					{name: t('Every quarter'), value: 15},
					{name: t('Every hour'), value: 60}
				]})
			)
		)

		this.addSharePanel();
	}
}