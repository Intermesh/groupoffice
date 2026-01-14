import {comp, fieldset, HtmlField, htmlfield, Notifier, root, t, textfield} from "@intermesh/goui";
import {client, customFields, FormFieldset, FormWindow, Image} from "@intermesh/groupoffice-core";
import {notebookcombo} from "./NoteBookCombo";
import {Note} from "./Index";

export class NoteDialog extends FormWindow<Note> {
	private contentFld: HtmlField;
	constructor() {
		super("Note");

		this.title = t("Note");

		this.stateId = "note-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.hasLinks = true;

		this.width = 800;
		this.height = 800;

		this.generalTab.cls = "fit";
		this.generalTab.items.add(
			fieldset({cls: " fit vbox gap"},
				comp({cls: "hbox gap"},
					textfield({
						flex: 1,
						name: "name",
						label: t("Name"),
						required: true
					}),

					notebookcombo({
						width: 240
					})
				),

				this.contentFld = htmlfield({
					name: "content",
					flex: 1,
					listeners: {

						insertimage: ({file, img}) => {
							root.mask();

							client.upload(file).then(r => {
								if (img) {
									img.dataset.blobId = r.id;
									img.removeAttribute("id");
								}
								Notifier.success(`Uploaded ${file.name} successfully`);
							}).catch((err) => {
								console.error(err);
								Notifier.error(`Failed to upload ${file.name}`);
							}).finally(() => {
								root.unmask();
							});
						}

					}
				})
			)
		)

		this.form.on("load", () => {
			void Image.replaceImages(this.contentFld.el).then(() => {
				this.contentFld.trackReset();
			})
		})

		this.addCustomFields();
	}


	protected addCustomFields() {
		//for notes all are tabs
		const fieldsets = customFields.getFieldSets(this.entityName).map(fs => new FormFieldset(fs))

		fieldsets.forEach((fs) => {
			//if (fs.fieldSet.isTab) {
				fs.title = fs.fieldSet.name;
				fs.legend = "";
				this.cards.items.add(fs);
		}, this);
	}
}