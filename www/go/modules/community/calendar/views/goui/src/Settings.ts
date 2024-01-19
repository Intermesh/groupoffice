import {checkbox, form, h3, t, textfield, Window} from "@intermesh/goui";

export class Settings extends Window {

	constructor() {
		super();
		this.title = "Settings";
		this.items.add(form({cls:'pad flow'},
			h3({html:'Video meeting'}),
			textfield({label: t('Server URL'), name: 'videoUri',}),
			checkbox({label: t("Enable JWT authentification"), name: "videoJwtEnabled", listeners: {

			}}),
			textfield({label: t('App Secret'), name: 'videoJwtSecret',}),
			textfield({label: t('App ID'), name: 'videoJwtAppId',}),
		));
	}
}