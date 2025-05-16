import {FormWindow, principalcombo} from "@intermesh/groupoffice-core";
import {fieldset, t, textfield} from "@intermesh/goui";

export class KeyDialog extends FormWindow {
	constructor() {
		super("Key");

		this.title = t("API key");
		this.stateId = "key-dialog";

		this.resizable = true;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				principalcombo({
					name: "userId",
					label: t("User"),
					required: true
				})
			)
		)

	}
}