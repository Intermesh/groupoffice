import {btn, datefield, fieldset, form, Form, t, tbar, textfield, Window} from "@intermesh/goui";

export class ImportKeyDialog extends Window {
	public form: Form;

	constructor() {
		super();

		this.title = t("Import key");
		this.height = 350;
		this.width = 500;

		this.resizable = false;
		this.maximizable = false;
		this.modal = true;


		this.items.add(
			this.form = form({
					flex: 1
				},
				fieldset({},
					textfield({
						name: "email",
						label: t("E-mail address"),
						hint: t("E-mail for which the imported key will be used"),
						required: true
					}),
					datefield({
						name: "expiresAt",
						label: t("Expires at"),
						required: true
					})
				)
			),
			tbar({
					cls: "border-top"
				},
				"->",
				btn({
					text: t("Upload file"),
					handler: () => {
						void this.form.submit();
						this.close();
					},
					type: "submit"
				})
			)
		);

	}
}