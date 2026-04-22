import {fieldset, hiddenfield, t, textfield} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class AddressBookGroupDialog extends FormWindow {
	constructor() {
		super("AddressBookGroup");

		this.title = t("Group");
		this.width = 600;
		this.height = "min-content";

		this.maximizable = false;

		this.generalTab.items.add(
			fieldset({},
				hiddenfield({
					name: "addressBookId",
				}),
				textfield({
					name: "name",
					label: t("Name")
				})
			)
		);
	}
}