import {FormWindow} from "@intermesh/groupoffice-core";
import {fieldset, t, textfield} from "@intermesh/goui";

export class CategoryDialog extends FormWindow {
	constructor() {
		super("BookmarksCategory");

		this.title = t("Category")
		this.stateId = "category-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.modal = true;

		this.width = 500;
		this.height = 500;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					flex: 1,
					name: "name",
					label: t("Name"),
					required: true
				})
			)
		);

		this.addSharePanel();
	}
}