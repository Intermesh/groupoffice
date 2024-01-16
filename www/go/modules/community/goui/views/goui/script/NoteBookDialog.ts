import {fieldset, t, textfield} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class NoteBookDialog extends FormWindow {
	constructor() {
		super("NoteBook");

		this.title = t("Note book");
		this.stateId = "note-book-dialog";

		this.generalTab.items.add(

			fieldset({},
				textfield({
					flex: 2,
					name: "name",
					label: t("Name"),
					required: true
				})
			)
		);

		this.addSharePanel();

	}
}