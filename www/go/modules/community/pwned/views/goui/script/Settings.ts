import {btn, ComboBox, combobox, comp, Component, containerfield, Fieldset, Notifier, t, tbar} from "@intermesh/goui";
import {groupDS, moduleDS, modules, ModuleSettingsFieldset} from "@intermesh/groupoffice-core";

export class Settings extends ModuleSettingsFieldset {
	constructor() {
		super("`;-- Have I been Pwned", "community", "pwned");
		void this.load();
	}

	protected formItems(): Component[] {
		return [
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
				dataSource: groupDS,
				// value: mod.settings.enableForGroupId
			})
		];
	}
}