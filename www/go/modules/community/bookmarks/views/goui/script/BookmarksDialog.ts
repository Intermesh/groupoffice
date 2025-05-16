import {FormWindow, jmapds, client} from "@intermesh/groupoffice-core";
import {
	browser,
	btn, Button,
	checkbox,
	CheckboxField,
	combobox, comp,
	fieldset, HiddenField, hiddenfield,
	t,
	TextField,
	textfield
} from "@intermesh/goui";

export class BookmarksDialog extends FormWindow {
	private readonly newTabCheckbox: CheckboxField
	private urlTextField: TextField
	private nameTextField: TextField
	private descriptionTextField: TextField
	private logoButton: Button
	private logoHiddenField: HiddenField

	constructor() {
		super("Bookmark");

		this.title = t("Bookmark");

		this.stateId = "bookmark-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.modal = true;

		this.height = 650;
		this.width = 500;

		this.generalTab.items.add(
			fieldset({},

				this.urlTextField = textfield({
					name: "content",
					type: "url",
					label: "URL",
					placeholder: "https://example.com",
					required: true,
					listeners: {
						change: async (field, newValue, oldValue) => {
							this.mask();

							await client.jmap("community/bookmarks/Bookmark/description", {
								url: newValue
							}).then(async (response) => {
								this.nameTextField.value = response.title;
								this.descriptionTextField.value = response.description;
								this.urlTextField.value = response.url;

								if (response.logo) {
									const blobURL = await client.getBlobURL(response.logo);

									this.logoButton.style = {
										backgroundImage: `url(${blobURL})`
									}

									this.logoHiddenField.value = response.logo;
								}

								this.unmask();
							})
						}
					}
				}),
				this.nameTextField = textfield({
					name: "name",
					label: t("Title"),
					required: true
				}),
				this.descriptionTextField = textfield({
					name: "description",
					label: t("Description")
				}),

				combobox({
					dataSource: jmapds("BookmarksCategory"),
					storeConfig: {
						sort: [{property: "name"}]
					},
					label: t("Category"),
					name: "categoryId",
					required: true,
					selectFirst: true
				}),

				this.logoHiddenField = hiddenfield({
					name: "logo",
					listeners: {
						setvalue: async (field, newValue, oldValue) => {
							const blobURL = await client.getBlobURL(newValue);

							this.logoButton.style = {
								backgroundImage: `url(${blobURL})`
							}
						}
					}
				}),
				comp({cls: "vbox"},
					comp({tagName: "h5", text: t("Logo"), style: {margin: "0 0.5rem"}}),
					this.logoButton = btn({
						cls: "outlined",
						style: {
							width: '32px',
							height: '32px',
							backgroundRepeat: 'no-repeat',
							backgroundSize: 'cover',
						},
						handler: async () => {
							const files = await browser.pickLocalFiles(false, false, "image/*");
							this.mask();
							const blob = await client.upload(files[0]);
							this.unmask();

							this.logoHiddenField.value = blob.id;
						}
					})
				),
				this.newTabCheckbox = checkbox({
					value: true,
					disabled: false,
					name: "openExtern",
					label: t("Open in new browser tab")
				}),
				checkbox({
					name: "behaveAsModule",
					label: t("Behave as a module (Browser reload required)"),
					listeners: {
						change: (field, newValue, oldValue) => {
							this.newTabCheckbox["disabled"] = newValue;

							if (newValue) {
								this.newTabCheckbox.value = false;
							}
						}
					}
				})
			)
		);
	}
}