import {btn, column, ComboBox, combobox, comp, displayfield, Field as FormField, t} from "@intermesh/goui";
import {CustomField, CustomFieldDialog, jmapds, CustomFieldTextDialog, CustomFieldType} from "@intermesh/groupoffice-core";

export class ContactCustomField extends CustomFieldType {
	constructor() {
		super(
			"Contact", "person", t("Contact")
		)
	}

	getDialog(): CustomFieldDialog {
		return new CustomFieldTextDialog();
	}


	private renderer = async (columnValue: any) => {
		if (!columnValue) {
			return "";
		}
		const u = await jmapds("Contact").single(columnValue);
		return u ? comp({tagName: "a", text: u.name, attr: {href: `#contact/${columnValue}`}}) : "";
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

		return combobox({
			...this.getFormFieldConfig(field),
			dataSource: jmapds("Contact"),
			filterName: "text",
			buttons: [btn({
				icon: "clear",
				handler: (button) => {
					button.findAncestorByType(ComboBox)!.value = null;
				}
			})],
			storeConfig: {
				filters: {
					default: filter
				}
			}
		})
	}
}

