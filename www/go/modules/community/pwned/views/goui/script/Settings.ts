import {combobox, comp, containerfield, Fieldset, t} from "@intermesh/goui";
import {groupDS} from "@intermesh/groupoffice-core";

export class Settings extends Fieldset {
	constructor() {
		super();

		this.legend = t("Settings");

		this.items.add(
			containerfield({
				name: "settings"
			},
				comp({
					html: t("Enable 'Have I Been Pwned' checking for users in a specific group")
				}),
				combobox({
					label: t("Group"),
					name: "enableForGroupId",
					valueProperty: "id",
					displayProperty: "name",
					placeholder: t("None"),
					clearable: true,
					required: true,
					dataSource: groupDS
				})
			)
		)
	}
}