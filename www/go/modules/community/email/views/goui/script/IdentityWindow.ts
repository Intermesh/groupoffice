import {
	btn,
	comp,
	DataSourceForm,
	datasourceform, datasourcestore,
	htmlfield,
	list,
	t,E,
	tbar,
	textarea,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class IdentityWindow extends Window {

	form: DataSourceForm

	constructor(){
		super();
		this.width = 880;
		this.height = 570;
		this.title = t('Identities');

		this.items.add(
			comp({cls:'hbox'},
				comp({tagName: 'aside'},
					comp({tagName:'nav'},
						comp({tagName:'h5', html: t('Identities')},
							comp({tagName:'span', cls: 'hover buttons'},
								btn({title: t('Add Identity'),cls:'small', icon: 'add',handler: (btn) => this.form.value = {}})
							)
						),
						list({
							store: datasourcestore({dataSource:jmapds('Identity')}),
							renderer: (d) => E('a').html(`<i class="icon">person</i><em>${d.name}</em>`),
							listeners: {'selectionchange': (me,items) => {this.form.load(items[0].id)}}
						}),
					),

					btn({tagName:'li',icon:'add', text: t('Add Identity'), handler: () => this.form.value = {}})

				),
				this.form = datasourceform({dataSource: jmapds('Identity'),flex:1},
					tbar({},
						textfield({cls: 'c6',placeholder:t('Display name'), name:'name'}),
						comp({tagName:'span', flex:1}),
						btn({icon:'delete'}),
						btn({text: t('Save'), cls: 'primary', icon:'save', type:'submit'}),
					),
					comp({cls: 'ff pad'},
						textfield({label: t('Email'), name: 'email'}),
						textfield({label:t('Reply to'), name: 'replyTo'}),
						textfield({label: t('Bcc'), name: 'bcc'}),
						textarea({label: t('Text signature'), name: 'textSignature', height: 200}),
						htmlfield({label: t('HTML Signature'), name: 'htmlSignature',height: 200})
					)
				)
			)
		);
	}
}