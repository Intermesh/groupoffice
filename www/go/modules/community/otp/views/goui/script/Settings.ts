import {checkbox, combobox, comp, containerfield, Fieldset, NumberField, numberfield, t} from "@intermesh/goui";
import {groupDS} from "@intermesh/groupoffice-core";

export class Settings extends Fieldset {
	private countDownFld: NumberField | undefined;

	constructor() {
		super();

		this.legend = t("Settings");

		this.items.add(
			containerfield({
					name: "settings"
				},
				comp({
					html: t("Enforce two factor authentication for users in a specific group")
				}),
				combobox({
					label: t("Group"),
					name: "enforceForGroupId",
					valueProperty: "id",
					displayProperty: "name",
					placeholder: t("None"),
					clearable: true,
					dataSource: groupDS,
				}),
				checkbox({
					type: "switch",
					id: "block",
					name: "block",
					label: t("Block Group-Office usage until setup is done", "community", "otp"),
					listeners: {
						setvalue: ({newValue, oldValue}) => {
							this.countDownFld!.disabled = newValue;
						}
					}
				}),
				this.countDownFld = numberfield({
					decimals: 0,
					width: 400,
					name: "countDown",
					label: t("Count down"),
					hint: t("Count down this number of seconds until the user can cancel the setup")
				})
			)
		);
	}
}