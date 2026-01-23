import {t, Fieldset, containerfield, textfield} from "@intermesh/goui";

export class Settings extends Fieldset {
	constructor() {
		super();

		this.legend = t("Settings");
		this.items.add(containerfield({
					name: "settings"
				},
				textfield({
					name: 'externalUrl',
					label: t('URL'),
					hint: t('Used as dokuwiki URL')
				}),
			textfield({
				name: 'title',
				label: t('Title'),
				hint: t('Used as dokuwiki page title')
			})
			)
		)
	}
}