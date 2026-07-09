
import {btn, column, ComboBox, comp, displayfield, Field as FormField, t, textfield, TextField} from "@intermesh/goui";
import {
	CustomField,
	CustomFieldDialog,
	customFields,
	CustomFieldTextDialog,
	CustomFieldType
} from "@intermesh/groupoffice-core";


export class CustomFieldFile extends CustomFieldType {
	constructor() {
		super("File", "star", t("File"));
	}

	getDialog(): CustomFieldDialog {
		return new CustomFieldTextDialog();
	}

	private renderer = (columnValue: any) => {
		if (!columnValue) {
			return "";
		}

		return comp({
			tagName: "a",
			text: columnValue,
			listeners: {
				render: ({target}) => {
					target.el.addEventListener("click", () => {
						GO.files.launchFile({
							path: columnValue
						})
					});
				}
			}
		});
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

		return textfield({
			...this.getFormFieldConfig(field),

			buttons: [btn({
				icon: "clear",
				handler: (button) => {
					button.findAncestorByType(ComboBox)!.value = null;
				}
			}),
				btn({
					icon: "folder",
					handler: (button) => {

						const field = button.findAncestorByType(TextField)!

						GO.files.createSelectFileBrowser();

						GO.selectFileBrowser.setFileClickHandler((r:any) => {
							if(r){
								field.value = r.data.path;
							}else
							{
								field.value = GO.selectFileBrowser.path;
							}

							GO.selectFileBrowserWindow.hide();
						}, this);


						GO.selectFileBrowser.setRootID(0, 0);
						GO.selectFileBrowserWindow.show();
					}
				})
			]

		})
	}
}

customFields.registerType(new CustomFieldFile);