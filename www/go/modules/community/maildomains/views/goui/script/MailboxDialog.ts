import {
	checkbox,
	comp, DefaultEntity,
	fieldset, NumberField,
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
	private quotaFld: NumberField;

	constructor() {
		super("MailBox");
		this.title = t("Mailbox");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		const minPasswordLength =  go.Modules.get("core","core").settings.passwordMinLength;

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
					attr: {
						minlength: minPasswordLength,
					},
					required: true,
					listeners: {
						validate: (fld) => {
							const v = fld.value as string, l = v.length,
								minValidationLength = fld.required ? 0 : 1;
							if (l >= minValidationLength && l < minPasswordLength) {
								fld.setInvalid(t("The minimum length for this field is {max}").replace("{max}", minPasswordLength));
							}
							const vc = this.passwordConfirmFld.value as string;
							if (vc && vc.length > 0 && v !== vc) {
								fld.setInvalid(t("The passwords do not match"));
							}
						}
					}
				}),
				this.passwordConfirmFld = textfield({
					name: "passwordConfirm",
					id: "passwordConfirm",
					label: t("Confirm password"),
					type: "password",
					required: true,
					attr: {
						minlength: minPasswordLength,
					},
					listeners: {
						validate: (fld) => {
							const v = fld.value as string, l = v.length,
								minValidationLength = fld.required ? 0 : 1;
							if (l >= minValidationLength && l < minPasswordLength) {
								fld.setInvalid(t("The minimum length for this field is {max}").replace("{max}", minPasswordLength));
							}
							const vc = this.passwordFld.value as string;
							if (vc && vc.length > 0 && v !== vc) {
								this.passwordFld.setInvalid(t("The passwords do not match"));
							}
						}
					}
				}),
				this.quotaFld = numberfield({
					name: "quota",
					id: "quota",
					label: t("Quota (MB)", "community", "maildomains"),
					decimals: 0,
					value: 0,
					required: true,
				}),
				checkbox({
					label: t("Active", "community", "maildomains"),
					name: "active",
					id: "active",
					type: "switch",
					value: true
				}),
				checkbox({
					label: t("Allow external SMTP usage", "community", "maildomains"),
					name: "smtpAllowed",
					id: "smtpAllowed",
					type: "switch"
				}),
				checkbox({
					label: t("Enable Full Text Search", "community", "maildomains"),
					name: "fts",
					id: "fts",
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
				this.quotaFld.value! /= 1024;
			} else {
				this.form.findField("domainId")!.value = this.entity!.id;
				this.quotaFld.value = this.entity!.defaultQuota / 1024;
			}
		});

		this.form.on("beforesave", (_f, v) => {
			if(v.quota) {
				v.quota *= 1024;
			}

			if(this.currentId) {
				if(v.password && v.password.length === 0) {
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