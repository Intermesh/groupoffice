import {
	checkbox,
	comp,
	DefaultEntity, fieldset,
	t,
	textfield
} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";

export class AliasDialog extends FormWindow {
	public entity: DefaultEntity|undefined;
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
					textfield({
						name: "domain",
						id: "domain",
						label: t("Domain"),
						disabled: true,
						icon: "alternate_email"
					}),
				),
				textfield({
					name: "domainId",
					id: "domainId",
					readOnly: true,
					required: true,
					hidden: true
				}),
				textfield({
					name: "goto",
					id: "goto",
					label: t("Goto"),
					hint: t("For multiple recipients use a comma separated list eg. alias1@domain.com,alias2@domain.com")
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch"
				}),
			)
		);

		this.on("ready", async () => {
			this.form.findField("domainId")!.value = this.entity!.id;
			if (!this.currentId) {
				this.form.findField("active")!.value = true
			} else {
				let address = this.form.findField("address")!.value as String;
				if (address.indexOf("@") > -1) {
					address = address.split("@")[0];
					if(address.length === 0) {
						address = "*";
					}
				}
				this.form.findField("address")!.value = address;
			}
			this.form.findField("domain")!.value = this.entity!.domain;
		});

		this.form.on("beforesave", (f, v) => {
			v.address = v.address+"@"+this.entity!.domain;
		});
	}

}

