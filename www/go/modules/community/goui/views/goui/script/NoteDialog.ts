import {notebookcombo} from "./NoteBookCombo.js";
import {comp, htmlfield, Notifier, root, t, textfield} from "@intermesh/goui";
import {client, FormWindow, Image} from "@intermesh/groupoffice-core";

export class NoteDialog extends FormWindow {

	constructor() {
		super("Note");

		this.title = t("Note");

		this.stateId = "note-dialog";
		this.maximizable = true;

		this.generalTab.items.add(
			comp({cls: "hbox gap"},
				textfield({
					flex: 2,
					name: "name",
					label: t("Name"),
					required: true
				}),

				notebookcombo({
					flex: 1
				}),
			),

			htmlfield({
				name: "content",
				listeners: {

					setvalue: (field, newValue, oldValue) => {
						Image.replaceImages(field.el);
					},

					insertimage: (htmlfield, file, img) => {
						root.mask();

						client.upload(file).then(r => {
							if (img) {
								img.dataset.blobId = r.id;
								img.removeAttribute("id");
							}
							Notifier.success("Uploaded " + file.name + " successfully");
						}).catch((err) => {
							console.error(err);
							Notifier.error("Failed to upload " + file.name);
						}).finally(() => {
							root.unmask();
						});
					}
				}
			})
		);

		this.addCustomFields();
	}

}