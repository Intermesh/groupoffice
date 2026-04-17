import {checkbox, fieldset, t, textarea, textfield} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class AddressBookDialog extends FormWindow {
	constructor() {
		super('AddressBook');

		this.title = t("Address book");
		this.height = 600;
		this.width = 700;

		this.maximizable = true;
		this.resizable = true;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				checkbox({
					name: "syncToDevice",
					label: t("Sync to device"),
					hint: t("Make address book available in CardDAV")
				})
			)
		);

		this.cards.items.add(
			fieldset({
				title: t("Advanced"),
			},
				textarea({
					name: "salutationTemplate",
					label: t("Salutation template"),
					autoHeight:  true,
					value: t("salutationTemplate", "community", "addressbook")
				})
			)
		);
	}
}