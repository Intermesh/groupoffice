import {Window, t, datasourceform, DataSourceForm, checkbox, textfield, select, comp} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class AccountWindow extends Window {

	form:DataSourceForm
	constructor() {
		super();
		this.title = t('Account');
		this.height = 800;

		this.items.add(
			this.form = datasourceform({
				dataSource: jmapds('EmailAccount')
			},
			comp({flex:1},
				textfield({label: t('Name'), name: 'name'}),
				textfield({label: t('Hostname'), name: 'hostname'}),
				textfield({label: t('Username'), name: 'user'}),
				textfield({label: t('Password'), name: 'pass', type: 'password'}),
				select({label: 'Security', name: 'security', options:[
					{name:t('SSL'), value: 'ssl'},
					{name:t('Start/TLS'), value: 'tls'},
					{name:t('Plain'), value: 'none'},
				]})
			))
		);
	}
}