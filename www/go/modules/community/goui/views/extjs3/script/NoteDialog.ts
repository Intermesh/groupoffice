import {Form, form} from "../../../../../../../views/Extjs3/goui/script/component/form/Form.js";
import {textfield} from "../../../../../../../views/Extjs3/goui/script/component/form/TextField.js";
import {t} from "../../../../../../../views/Extjs3/goui/script/Translate.js";
import {Window} from "../../../../../../../views/Extjs3/goui/script/component/Window.js";
import {htmlfield} from "../../../../../../../views/Extjs3/goui/script/component/form/HtmlField.js";
import {EntityStore} from "../../../../../../../views/Extjs3/goui/script/api/EntityStore.js";
import {client} from "../../../../../../../views/Extjs3/goui/script/api/Client.js";
import {tbar} from "../../../../../../../views/Extjs3/goui/script/component/Toolbar.js";
import {btn} from "../../../../../../../views/Extjs3/goui/script/component/Button.js";
import {Fieldset, fieldset} from "../../../../../../../views/Extjs3/goui/script/component/form/Fieldset.js";
import {notebookcombo} from "./NoteBookCombo.js";
import {Notifier} from "../../../../../../../views/Extjs3/goui/script/Notifier.js";
import {root} from "../../../../../../../views/Extjs3/goui/script/component/Root.js";
import {CardContainer, cards} from "../../../../../../../views/Extjs3/goui/script/component/CardContainer.js";
import {cardmenu} from "../../../../../../../views/Extjs3/goui/script/component/CardMenu.js";
import {comp} from "../../../../../../../views/Extjs3/goui/script/component/Component.js";
import {containerfield} from "../../../../../../../views/Extjs3/goui/script/component/form/ContainerField.js";

export class NoteDialog extends Window {
	readonly form: Form;
	private entityStore: EntityStore;
	private currentId?: number;
	private cards: CardContainer;
	private general: Fieldset;

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
							Window.alert(t("Error"), e);
						} finally {
							this.unmask();
						}
					}
				},
				cardmenu(),

				this.cards = cards({flex: 1},
					this.general = fieldset({cls: "scroll fit", title: t("General")},

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
					)
				),


				tbar({cls: "border-top"},
					"->",
					btn({
						type: "submit",
						text: t("Save")
					})
				)
			)
		)

		this.addCustomFields();
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


	private addCustomFields() {
		const es = "Note"
		if (go.Entities.get(es).customFields) {
			var fieldsets = go.customfields.CustomFields.getFormFieldSets(es);
			fieldsets.forEach((fs:any) => {

				//replace customFields. because we will use a containerfield here.
				fs.cascade((item:any) => {
					if(item.getName) {
						let fieldName = item.getName().replace('customFields.', '');
						item.name = fieldName;
					}
				});

				if (fs.fieldSet.isTab) {
					fs.title = null;
					fs.collapsible = false;

					this.cards.items.add(containerfield({name: "customFields", cls: "scroll", title: fs.fieldSet.name}, fs));
				} else {
					//in case formPanelLayout is set to column
					fs.columnWidth = 1;
					this.general.items.add(containerfield({name: "customFields"}, fs));
				}
			}, this);
		}
	}
}