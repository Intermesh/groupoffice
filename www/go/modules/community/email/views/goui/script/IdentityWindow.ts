import {
	btn,
	comp,
	DataSourceForm,
	datasourceform, datasourcestore,
	htmlfield,
	list,
	t, E,
	tbar,
	textarea,
	textfield,
	Window, fieldset
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";

export class IdentityWindow extends FormWindow {

	protected accountId?: string
	constructor(forAccountId: string){
		super('Identity');
		this.width = 880;
		this.height = 810;
		this.title = t('Identitiy');
		this.accountId = forAccountId;

		this.generalTab.items.add(
			fieldset({cls: 'flow', flex:'1 0'},
				textfield({placeholder:t('Display name'), name:'name'}),
				textfield({flex:'.5',label: t('From email'), name: 'email'}),
				textfield({flex:'.5',label:t('Reply to'), name: 'replyTo'}),
				textfield({flex:'.5',label: t('Bcc'), name: 'bcc'}),
				textarea({ label: t('Text signature'), name: 'textSignature', height: 200}),
				htmlfield({label: t('HTML Signature'), name: 'htmlSignature', height: 300})
			)
		);

		this.form.on('beforesave', ({data}) => {
			jmapds('Identity').setParams.accountId = this.accountId;
		});
	}

}