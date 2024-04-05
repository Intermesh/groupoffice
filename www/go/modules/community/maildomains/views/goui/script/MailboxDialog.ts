import {
	checkbox,
	comp, DefaultEntity,
	fieldset,
	numberfield,
	t, TextField,
	textfield,
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class MailboxDialog extends FormWindow {
	public entity: DefaultEntity|undefined;

	private usernameFld: TextField;
	private passwordFld: TextField;
	private passwordConfirmFld: TextField;

	constructor() {
		super("MailBox");
		this.title = t("Mailbox");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.generalTab.items.add(
			fieldset({flex: 1},
				comp({cls: "row"},
					this.usernameFld = textfield({
						name: "username",
						id: "username",
						label: t("Username"),
						required: true,
						flex: 0.5
					}),
					textfield({
						icon: "alternate_email",
						disabled: true,
						name: "domain",
						id: "domain",
						flex: 0.5
					})
				),
				textfield({
					name: "name",
					id: "name",
					required: true,
				}),
				textfield({
					name: "domainId",
					id: "domainId",
					readOnly: true,
					required: true,
					hidden: true
				}),
				this.passwordFld = textfield({
					name: "password",
					id: "password",
					label: t("Password"),
					type: "password",
					required: true
				}),
				this.passwordConfirmFld = textfield({
					name: "passwordConfirm",
					id: "passwordConfirm",
					label: t("Confirm password"),
					type: "password",
					required: true
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
					name: "smtpAllowed",
					id: "smtpAllowed",
					type: "switch"
				})
			)
		);

		this.on("ready", async  () => {
			this.form.findField("domain")!.value = this.entity!.domain;
			if (this.currentId) {
				this.usernameFld.readOnly = true;
				const username = String(this.usernameFld.value);
				if (username.indexOf("@") >-1) {
					this.usernameFld.value = username.split("@")[0];
				}
				this.passwordFld.required = false;
				this.passwordConfirmFld.required = false;
				this.passwordFld.value = "";
			} else {
				this.form.findField("domainId")!.value = this.entity!.id;
				this.form.findField("active")!.value = true;
			}
		});

		this.form.on("beforesave", (f, v) => {
			if(this.currentId) {
				if(v.password.length === 0) {
					delete v.password;
				}
				delete v.username; // This may never change!
			} else {
				v.username += "@" + this.entity!.domain;
			}
			delete v.passwordConfirm;
		})
	}
}