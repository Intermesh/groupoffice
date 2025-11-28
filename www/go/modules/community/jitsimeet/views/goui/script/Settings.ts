import {checkbox, containerfield, h3, textfield, Component, t, datasourceform, fieldset} from "@intermesh/goui";
import {jmapds, modules} from "@intermesh/groupoffice-core";

export class Settings extends Component {
	private form;

	constructor() {
		super();
		this.title = t("Settings");

		this.form = datasourceform({
				dataSource: jmapds("Module")
			},

			fieldset({},

				containerfield({
						name: "settings"
					},

					h3({html:t('Video meeting')}),
					textfield({label: t('Server URL'), name: 'videoUri',}),
					checkbox({label: t("Enable JWT authentification"), name: "videoJwtEnabled"}),
					textfield({type: "password", label: t('App Secret'), name: 'videoJwtSecret'}),
					textfield({label: t('App ID'), name: 'videoJwtAppId',}),

				)
			)
		);


		this.items.add(this.form);

		const mod = modules.get("community", "jitsimeet");

		if(mod) {
			this.form.load(mod.id);
		}
	}

	onSubmit() {
		return this.form.submit();
	}
}