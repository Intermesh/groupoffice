import {checkbox, containerfield, fieldset, Fieldset, h3, t, textfield} from "@intermesh/goui";

export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("Settings");

		this.items.add(fieldset({},

			containerfield({
					name: "settings"
				},

				h3({html: t('Video meeting')}),
				textfield({label: t('Server URL'), name: 'videoUri',}),
				checkbox({label: t("Enable JWT authentification"), name: "videoJwtEnabled"}),
				textfield({type: "password", label: t('App Secret'), name: 'videoJwtSecret'}),
				textfield({label: t('App ID'), name: 'videoJwtAppId',}),
			)
		))
	}
}