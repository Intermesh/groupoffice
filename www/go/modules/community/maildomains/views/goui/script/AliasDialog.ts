import {
	checkbox,
	comp,
	DefaultEntity, fieldset,
	t, textarea, TextField,
	textfield
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class AliasDialog extends FormWindow {
	private domainFld: TextField;
	constructor() {
		super("MailAlias");

		this.title = t("Alias");
		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.generalTab.items.add(
			fieldset({flex: 1},
				comp({cls: "row"},
					textfield({
						name: "address",
						id: "address",
						label: t("Address"),
						required: true,
						hint: t("Use '*' for a catch all alias (not recommended)."),
					}),
					this.domainFld = textfield({
						name: "domain",
						id: "domain",
						label: t("Domain"),
						readOnly: true,
						icon: "alternate_email"
					}),
				),
				textarea({
					autoHeight: true,
					name: "goto",
					id: "goto",
					label: t("Goto"),
					hint: t("For multiple recipients use a comma separated list eg. alias1@domain.com,alias2@domain.com")
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch",
					value: true
				}),
			)
		);

		this.on("ready", async () => {

			if (this.form.currentId) {
				const idField = 	this.form.findField("address")!;
				let address = idField.value as String;
				if (address.indexOf("@") > -1) {
					const parts = address.split("@")
					address = parts[0];
					if(address.length === 0) {
						address = "*";
					}

					this.domainFld.value = parts[1];
				}
				idField.value = address;

				idField.readOnly = true;

				this.form.trackReset();
			}
		});

		this.form.on("beforesave", (f, v) => {
			if(!this.form.currentId) {
				v.address = v.address + "@" + this.form.findField("domain")!.value;
			}
		});
	}

}

