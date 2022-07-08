import {Form, form} from "../../../../../../../views/Extjs3/goui/script/component/form/Form.js";
import {textfield} from "../../../../../../../views/Extjs3/goui/script/component/form/TextField.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {htmlfield} from "../../../../../../../views/Extjs3/goui/script/component/form/HtmlField.js";
import {EntityStore} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {fieldset} from "../../../../../../../views/Extjs3/goui/script/component/form/Fieldset.js";
import {notebookcombo} from "./NoteBookCombo.js";
import {Notifier} from "../../../../../../../views/Extjs3/goui/script/Notifier.js";
import {root} from "../../../../../../../views/Extjs3/goui/script/component/Root.js";

export class NoteDialog extends Window {
	private form: Form;
	private entityStore: EntityStore;
	private currentId?: number;

	constructor() {
		super();

		this.entityStore = new EntityStore("Note", client);

		this.cls = "vbox";
		this.title = t("Note");
		this.width = 600;
		this.height = 400;
		this.stateId = "note-dialog";
		this.maximizable = true;

		this.items.add(
			this.form = form(
				{
					cls: "vbox",
					flex: 1,
					handler: async (form) => {
						try {
							await this.entityStore.save(form.value, this.currentId);
							this.close();
						} catch (e) {
							Window.alert(t("Error"), e + "");
						} finally {
							this.unmask();
						}
					}
				},

				fieldset({cls: "scroll", flex: 1},

					notebookcombo(),

					textfield({
						name: "name",
						label: t("Name"),
						required: true
					}),

					htmlfield({
						name: "content",
						listeners: {
							insertimage: (htmlfield, file, img) => {
								root.mask();

								client.upload(file).then(r => {
									if (img) {
										img.dataset.blobId = r.blobId;
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

				),

				tbar({},
					"->",
					btn({
						type: "submit",
						text: t("Save")
					})
				)
			)
		)
	}

	public async load(id: number) {

		this.mask();

		try {
			this.form.value = await this.entityStore.single(id);
			this.currentId = id;
		} catch (e) {
			Window.alert(t("Error"), e + "");
		} finally {
			this.unmask();
		}

		return this;
	}
}