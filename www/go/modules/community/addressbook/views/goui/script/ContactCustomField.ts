import {btn, column, ComboBox, combobox, comp, displayfield, Field as FormField, t} from "@intermesh/goui";
import {Field, FieldDialog, jmapds, TextDialog, Type} from "@intermesh/groupoffice-core";

export class ContactCustomField extends Type {
	constructor() {
		super(
			"Contact", "person", t("Contact")
		)
	}

	getDialog(): FieldDialog {
		return new TextDialog();
	}


	private renderer = async (columnValue: any) => {
		if (!columnValue) {
			return "";
		}
		const u = await jmapds("Contact").single(columnValue);
		return u ? comp({tagName: "a", text: u.name, attr: {href: `#contact/${columnValue}`}}) : "";
	}
	createTableColumField(field:Field) {
		return column({
			...this.getColumnConfig(field),
			width: 100,
			renderer: this.renderer
		})
	}

	createDetailField(field:Field) {
		return displayfield({
			...this.getDetailFieldConfig(field),
			renderer: this.renderer
		});
	}

	createFormField(field:Field): FormField {

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

