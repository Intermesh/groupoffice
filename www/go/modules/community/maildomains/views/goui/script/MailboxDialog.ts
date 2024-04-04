import {
	btn,
	checkbox,
	comp, DefaultEntity, EntityID,
	fieldset, Form, form,
	numberfield,
	t, tbar, TextField,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds, userdisplaycombo} from "@intermesh/groupoffice-core";

export class MailboxDialog extends Window {
	public currentId: EntityID|undefined;

	public entity: DefaultEntity;

	private form: Form;

	private usernameFld: TextField;
	private passwordFld: TextField;
	private passwordConfirmFld: TextField;

	constructor(entity: DefaultEntity) {
		super();
		this.entity = entity;
		this.title = t("Mailbox");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.form = form({
			cls: "vbox",
			flex: 1,
			handler: (f) => {
				// TODO: Validate password & passwordConfirmation
				// TODO: Refactor. This code is a bit messy
				const values = f.value;
				delete values.passwordConfirm;
				if(values.password.length === 0 && this.currentId) {
					delete values.password;
				}
				let mb = values;
				if(this.currentId) {
					mb = this.entity.mailboxes.find((m: any) => {return m.id == this.currentId});
					Object.assign(mb, values);
				}
				mb.quota *= 1024;
				mb.username += "@"+this.entity.domain;
				if(!this.currentId) {
					this.entity.mailboxes.push(mb);
				}
				jmapds("MailDomain").update(this.entity.id, {
					mailboxes: this.entity.mailboxes
				} );
				this.close();
			}
			},
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
						flex: 0.5,
						value: this.entity.domain
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

		this.items.add(this.form,
			tbar({cls: "border-top"},
				"->",
				btn({cls: "filled primary", text: t("Save"), handler: (_btn) => {this.form.submit();}}))
		);

		this.on("render", async  () => {
		});
	}

	public load(record: any) {
		if(record.id) {
			this.currentId = record.id;
			record.password = "";
			this.usernameFld.readOnly = true;
			this.passwordFld.required = false;
			this.passwordConfirmFld.required = false;
			if(record.username.indexOf("@") > -1) {
				record.username = record.username.split("@")[0];
			}
			record.quota /= 1024;
		}
		this.form.value = record;

	}

}