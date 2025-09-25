import {
	checkbox, chips,
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
						disabled: true,
						icon: "alternate_email"
					}),
				),
				chips({
					name: "recipients",
					id: "recipients",
					label: t("Goto")
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

		this.form.on("beforesave", ({data}) => {
			if(!this.form.currentId) {
				data.address = data.address + "@" + this.form.findField("domain")!.value;
			}
		});
	}

}

