import {t, textfield, select, comp, containerfield} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class AccountWindow extends FormWindow {

	constructor() {
		super('EmailAccount');
		this.title = t('Account');
		this.height = 800;

		this.generalTab.items.add(
			textfield({label: t('Name'), name: 'name'}),
			textfield({label: t('E-Mail'), name: 'email'}),
			//mda
			containerfield({name:'mda'},
				textfield({label: t('Hostname'), name: 'host'}),
				textfield({label: t('Username'), name: 'user'}),
				textfield({label: t('Password'), name: 'pass', type: 'password'}),
				select({label: 'Security', name: 'encryption', options:[
					{name:t('SSL'), value: 'ssl'},
					{name:t('Start/TLS'), value: 'tls'},
					{name:t('Plain'), value: 'none'},
				]})
			),
			containerfield({name:'mta'},
				textfield({label: t('Hostname'), name: 'host'}),
				textfield({label: t('Username'), name: 'user'}),
				textfield({label: t('Password'), name: 'pass', type: 'password'}),
				select({label: 'Security', name: 'encryption', options:[
					{name:t('SSL'), value: 'ssl'},
					{name:t('Start/TLS'), value: 'tls'},
					{name:t('Plain'), value: 'none'},
				]})
			)
		);
	}
}