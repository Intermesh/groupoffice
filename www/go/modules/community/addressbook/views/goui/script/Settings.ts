import {
	a,
	autocompletechips,
	checkbox, checkboxselectcolumn, column,
	containerfield, datasourcestore,
	displayfield,
	Fieldset,
	h3,
	radio,
	t, table,
	textfield
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class Settings extends Fieldset {

	constructor() {
		super();
		this.legend = t("Settings");

		this.items.add(
			containerfield({
				name: "settings"
			},

			checkbox({
				name: "createPersonalAddressBooks",
				label: t("Create personal address book for each user")
			}),

			radio({
				name:"autoLink",
				cls: "vertical-stack",
				label: t("Automatic e-mail linking"),
				options: [
					{text: t("Don't link automatically to contacts"), value: "off"},
					{text: t("Link to all contacts"), value: "on"},
					{text: t("Exclude contacts from the address books below"), value: "excl"},
					{text: t("Only link to the contacts from the address books below"), value: "incl"},
				],
				listeners: {
					setvalue: ({target, newValue}) => {
						target.nextSibling()!.disabled = newValue !== "incl"  && newValue !== "excl";
					}
				}

			}),

			autocompletechips({
				disabled: true,
				list: table({
					fitParent: true,
					headers: false,
					store: datasourcestore({
						dataSource: jmapds("AddressBook"),
					}),
					rowSelectionConfig: {
						multiSelect: true
					},
					columns: [
						checkboxselectcolumn(),
						column({
							header: "Name",
							id: "name",
							sortable: true,
							resizable: true
						})
					]
				}),
				label: t("Address book"),
				name: "autoLinkAddressBookIds",
				chipRenderer: async (chip, value) => {
					chip.text = (await jmapds("AddressBook").single(value)).name;
				},
				pickerRecordToValue(field, record): any {
					return record.id;
				},
				listeners: {
					autocomplete: ({target, input}) => {
						target.list.store.setFilter("autocomplete", {text: input});
						void target.list.store.load();
					}
				}
			})


		));
	}

}