import {checkbox, containerfield, Fieldset, t, textfield} from "@intermesh/goui";

/**
 * Module settings shown in the module dialog. The dialog's form loads and saves the "settings" of the module.
 */
export class SystemSettings extends Fieldset {
	constructor() {
		super();
		this.legend = t('Video meeting', "community", "jitsimeet");

		this.items.add(
			containerfield({
					name: "settings"
				},
				textfield({label: t('Server URL', "community", "jitsimeet"), name: 'videoUri'}),
				checkbox({label: t("Enable JWT authentication", "community", "jitsimeet"), name: "videoJwtEnabled"}),
				textfield({type: "password", label: t('App Secret', "community", "jitsimeet"), name: 'videoJwtSecret'}),
				textfield({label: t('App ID', "community", "jitsimeet"), name: 'videoJwtAppId'})
			)
		);
	}
}
