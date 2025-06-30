import {
	checkbox,
	comp, containerfield, DefaultEntity,
	fieldset, br, NumberField,
	numberfield, p, select,
	t, TextField,
	textfield, hr,
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class MailboxDialog extends FormWindow {
	public domainEntity: DefaultEntity|undefined;

	private usernameFld: TextField;
	private passwordFld: TextField;
	private passwordConfirmFld: TextField;
	private quotaFld: NumberField;
	private domainFld: TextField;

	constructor() {
		super("MailBox");
		this.title = t("Mailbox");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;
		this.height = 800;

		const minPasswordLength =  go.Modules.get("core","core").settings.passwordMinLength;


		this.generalTab.items.add(
			fieldset({},
				comp({cls: "row"},
					this.usernameFld = textfield({
						name: "username",
						id: "username",
						label: t("Username"),
						required: true,
						flex: 0.5
					}),
					this.domainFld = textfield({
						icon: "alternate_email",
						disabled: true,
						name: "domain",
						id: "domain",
						flex: 0.5
					})
				),
			),

			comp({cls: "hbox"},
				fieldset({flex: 1},

					textfield({
						label: t("Description"),
						name: "description",
						id: "description",
						required: false,
					}),

					hr(),

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
							validate: ({target}) => {
								const v = target.value as string, l = v.length,
									minValidationLength = target.required ? 0 : 1;
								if (l >= minValidationLength && l < minPasswordLength) {
									target.setInvalid(t("The minimum length for this field is {max}").replace("{max}", minPasswordLength));
								}
								const vc = this.passwordConfirmFld.value as string;
								if (vc && vc.length > 0 && v !== vc) {
									target.setInvalid(t("The passwords do not match"));
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
							validate: ({target}) => {
								const v = target.value as string, l = v.length,
									minValidationLength = target.required ? 0 : 1;
								if (l >= minValidationLength && l < minPasswordLength) {
									target.setInvalid(t("The minimum length for this field is {max}").replace("{max}", minPasswordLength));
								}
								const vc = this.passwordFld.value as string;
								if (vc && vc.length > 0 && v !== vc) {
									this.passwordFld.setInvalid(t("The passwords do not match"));
								}
							}
						}
					}),

					hr(),

					this.quotaFld = numberfield({
						name: "quota",
						id: "quota",
						label: t("Quota (MB)", "community", "maildomains"),
						decimals: 0,
						value: 0,
						required: true,
						multiplier: 1 / (1024 * 1024) // convert bytes to MB
					}),

				),

				comp({flex: 1},

					fieldset({},

						checkbox({
							label: t("Active", "community", "maildomains"),
							name: "active",
							id: "active",
							type: "switch",
							value: true
						}),

						checkbox({
							label: t("Domain owner"),
							type: "switch",
							value: false,
							name: "domainOwner",
							hint: t("When enabled this user can login to all mailboxes of the domain using user@example.com*thisuser@example.com")
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
						}),

					),

					fieldset({},



						containerfield({
								name: "autoExpunge",

								listeners: {

									beforesetvalue: (e) => {
										if(e.value == "0") {
											e.value = {enabled: false, days: 30}
										} else {
											e.value = {enabled: true, days: parseInt(e.value.substring(0, e.value.length-1))}
										}
										e.target.findField("days")!.disabled = !e.value.enabled;

									},

									beforegetvalue: ( e) => {

										if(!e.value.enabled) {
											e.value = "0";
										} else {
											e.value = e.value.days + "d";
										}
									}
								},
							},
							checkbox({
								label: t("Auto expunge"),
								type: "switch",
								value: true,
								name: "enabled",
								listeners: {
									change:( {newValue, target}) => {
										const p = target.nextSibling()!
										p.disabled = !newValue;
										p.nextSibling()!.disabled = !newValue;
									},
								}
							}),

							p({text: t("Automatically delete mail from the Trash and Spam folder after a period of time.")}),

							numberfield({
								flex: 1,
								name: "days",
								decimals: 0,
								label: t("Expunge after days"),
								value: 30
							})
						),



					),
				)
			)
		);

		this.on("ready", async  () => {
			if (this.form.currentId) {
				this.usernameFld.readOnly = true;
				const username = this.usernameFld.value as string;
				if (username.indexOf("@") >-1) {
					const parts = username.split("@")
					this.usernameFld.value = parts[0];

					this.domainFld.value = parts[1];
				}
				this.passwordFld.required = false;
				this.passwordConfirmFld.required = false;
				this.passwordFld.value = "";
			}

			this.form.trackReset();
		});

		this.form.on("beforesave", ({data}) => {

			if(this.form.currentId) {
				if(data.password && data.password.length === 0) {
					delete data.password;
				}
			} else {
				data.username += "@" + this.form.findField("domain")!.value;
			}
			delete data.passwordConfirm;
		})
	}
}