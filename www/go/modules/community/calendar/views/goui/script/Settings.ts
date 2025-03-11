import {checkbox, containerfield, h3, textfield} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";

export class Settings extends FormWindow {

	constructor() {
		super('Module');
		this.title = t("Settings");

		this.form.items.insert(0,containerfield({cls:'pad flow', name:'settings'},
			h3({html:t('Video meeting')}),
			textfield({label: t('Server URL'), name: 'videoUri',}),
			checkbox({label: t("Enable JWT authentification"), name: "videoJwtEnabled", listeners: {

			}}),
			textfield({type: "password", label: t('App Secret'), name: 'videoJwtSecret',}),
			textfield({label: t('App ID'), name: 'videoJwtAppId',}),
		));
	}

	openLoad() {
		const m = go.Modules.get('community', 'calendar');
		this.load(m.id).then(m => m.show());
		// modules.getAll().then(_ => {
		// 	const m = modules.get('community', 'calendar');
		// 	this.load(m.id).then(m => m.show());
		// });
	}
}