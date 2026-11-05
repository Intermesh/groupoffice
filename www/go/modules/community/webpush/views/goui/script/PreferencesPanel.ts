import {btn, containerfield, fieldset,} from "@intermesh/goui";
import {AppSettingsPanel} from "@intermesh/groupoffice-core";
import {t} from "./Index.js";


export class PreferencesPanel extends AppSettingsPanel {

	constructor() {
		super();
		this.title = t('Push subscription');

		this.items.add(containerfield({},
			fieldset({},
				btn({text: t('Subscribe')}).on('click', () => {

				})
			)
		));
	}
}