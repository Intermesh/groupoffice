import {containerfield, Fieldset, t, textfield} from "@intermesh/goui";

export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("Settings");

		this.items.add(containerfield({
				name: "settings"
			},
			textfield({
				label: t("Mail hostname"),
				name: "mailHost",
				hint: t("The hostname of the mail system. Used for PTR, SPF and MX checks.")
			})

		));
	}
}