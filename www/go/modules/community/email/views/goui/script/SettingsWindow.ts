import {t, Window} from "@intermesh/goui";

export class SettingsWindow extends Window {

	constructor() {
		super();
		this.title = t('Settings');
	}
}