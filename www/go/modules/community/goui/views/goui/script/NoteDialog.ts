import {notebookcombo} from "./NoteBookCombo.js";
import {comp, fieldset, htmlfield, Notifier, root, t, textfield} from "@intermesh/goui";
import {client, FormWindow, Image} from "@intermesh/groupoffice-core";

export class NoteDialog extends FormWindow {

	constructor() {
		super("Note");

		this.title = t("Note");

		this.stateId = "note-dialog";
		this.maximizable = true;

		this.width = 800;
		this.height = 800;

		this.generalTab.items.add(

			fieldset({},

				textfield({
					flex: 1,
					name: "name",
					label: t("Name"),
					required: true
				}),

				notebookcombo({
					width: 240
				}),


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
			)
		);

		this.addCustomFields();
	}

}