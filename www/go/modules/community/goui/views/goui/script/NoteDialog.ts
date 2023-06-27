import {notebookcombo} from "./NoteBookCombo.js";
import {
	btn,
	CardContainer,
	cardmenu,
	cards,
	comp,
	containerfield,
	Fieldset,
	fieldset,
	form,
	Form,
	htmlfield,
	Notifier,
	root,
	t,
	tbar,
	textfield,
	Window,
	EntityID, datasourceform, DataSourceForm
} from "@intermesh/goui";
import {client, Image, jmapds} from "@intermesh/groupoffice-core";

export class NoteDialog extends Window {
	readonly form: DataSourceForm;

	private currentId?: EntityID;
	private cards: CardContainer;
	private general: Fieldset;

	constructor() {
		super();

		this.cls = "vbox";
		this.title = t("Note");
		this.width = 600;
		this.height = 400;
		this.stateId = "note-dialog";
		this.maximizable = true;

		this.items.add(
			this.form = datasourceform(
				{
					dataSource: jmapds("Note"),
					cls: "vbox",
					flex: 1,
					listeners: {
						submit: ()=> {
							this.close();
						}
					}
				},
				cardmenu(),

				this.cards = cards({flex: 1},
					this.general = fieldset({cls: "scroll fit", title: t("General")},

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
						}),



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

	public async load(id: EntityID) {

		this.mask();

		try {
			await this.form.load(id);
			this.currentId = id;
		} catch (e) {
			void Window.alert(t("Error"), e + "");
		} finally {
			this.unmask();
		}

		return this;
	}


	private addCustomFields() {
		const es = "Note"
		if (go.Entities.get(es).customFields) {
			const fieldsets = go.customfields.CustomFields.getFormFieldSets(es);
			fieldsets.forEach((fs: any) => {

				//replace customFields. because we will use a containerfield here.
				fs.cascade((item: any) => {
					if (item.getName) {
						let fieldName = item.getName().replace('customFields.', '');
						item.name = item.hiddenName =  fieldName;
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