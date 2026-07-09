
import {
	autocompletechips,
	AutocompleteChips,
	btn,
	column,
	comp,
	datasourcestore,
	displayfield,
	Field as FormField,
	t,
	table
} from "@intermesh/goui";
import {CustomField, CustomFieldDialog, jmapds, CustomFieldTextDialog, CustomFieldType} from "@intermesh/groupoffice-core";


export class MultiContactCustomField extends CustomFieldType {
	constructor() {
		super("MultiContact", "person", t("Contact") + " (Multiple)");
	}

	getDialog(): CustomFieldDialog {
		return new CustomFieldTextDialog();
	}

	private renderer = async (columnValue: any) => {

		const response = await jmapds("Contact").get(columnValue);
		return comp({cls:"comma-list"}, ...response.list.map(c => comp({tagName: "a", text: c.name, attr: {href: `#contact/${c.id}`}})));
	}
	createTableColumField(field:CustomField) {
		return column({
			...this.getColumnConfig(field),
			width: 100,
			renderer: this.renderer
		})
	}

	createDetailField(field:CustomField) {
		return displayfield({
			...this.getDetailFieldConfig(field),
			renderer: this.renderer
		});
	}

	createFormField(field:CustomField): FormField {

		const filter:any = {isOrganization: field.options.isOrganization};

		if(field.options.addressBookId?.length) {
			filter.addressBookId = field.options.addressBookId;
		}

		const store =datasourcestore({
			dataSource:  jmapds("Contact"),
			filters: {default: filter}
		});

		return autocompletechips({
			...this.getFormFieldConfig(field),

			chipRenderer: async (chip, value) => {
				chip.text = (await jmapds("Contact").single(value)).name;
			},
			pickerRecordToValue (field, record) : any {
				return record.id;
			},

			listeners: {
				autocomplete: ({input}) => {
					store.setFilter("search", {text: input});
					void store.load();
				}
			},


			buttons: [btn({
				icon: "clear",
				handler: (button) => {
					button.findAncestorByType(AutocompleteChips)!.value = [];
				}
			})],

			list: table({
				fitParent: true,
				headers: false,
				store: store,
				columns: [
					column({
						id: "name"
					})
				]
			})
		});
	}
}

