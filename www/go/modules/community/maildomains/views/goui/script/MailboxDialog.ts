import {
	btn,
	checkbox,
	comp, DefaultEntity, EntityID,
	fieldset, Form, form,
	numberfield,
	t, tbar,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds, userdisplaycombo} from "@intermesh/groupoffice-core";

export class MailboxDialog extends Window {
	public currentId: EntityID|undefined;

	public entity: DefaultEntity | undefined;

	public domain: string | undefined;

	private form: Form;

	constructor() {
		super();

		this.title = t("Alias");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;
		this.height = 600;

		this.form = form({},
			fieldset({flex: 1},
				comp({cls: "row"},
					textfield({
						name: "username",
						id: "username",
						label: t("Username"),
						required: true,
						readOnly: !!this.currentId,
						flex: 0.5
					}),
					textfield({
						disabled: true,
						name: "domain",
						id: "domain",
						flex: 0.5,
						value: this.domain
					})
				),
				textfield({
					name: "password",
					id: "password",
					label: t("Password"),
					type: "password",
					required: !this.currentId
				}),
				textfield({
					name: "domainId",
					id: "domainId",
					readOnly: true,
					required: true
				}),
				textfield({
					name: "passwordConfirm",
					id: "passwordConfirm",
					label: t("Confirm password"),
					type: "password",
					required: !this.currentId
				}),
				numberfield({
					name: "quota",
					id: "quota",
					label: t("Quota (MB)"),
					decimals: 0,
					required: true
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch"
				}),
				checkbox({
					label: t("Allow external SMTP usage"),
					name: "allowSmtp",
					id: "allowSmtp",
					type: "switch"
				})
			)
		);

		this.items.add(this.form, tbar({}, "->", btn({text: t("Save"), handler: (btn) => {console.log("TODO")}}))
		);

		this.on("render", async  () => {
		});
	}

	public load(record: any) {
		debugger;
		this.form.value = record;
		console.log(record);
		if(record.id) {
			this.currentId = record.id;
		}
	}
}