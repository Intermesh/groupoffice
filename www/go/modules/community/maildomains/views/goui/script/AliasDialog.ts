import {
	btn,
	checkbox,
	comp,
	Component,
	DefaultEntity, EntityID,
	fieldset, form,Form,
	Notifier,
	numberfield,
	searchbtn,
	t, tbar,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";

export class AliasDialog extends Window {
	public currentId: EntityID|undefined;

	private form: Form;

	public entity: DefaultEntity;

	constructor(entity: DefaultEntity) {
		super();

		this.title = t("Alias");
		this.entity = entity;
		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.form = form({
				cls: "vbox",
				flex: 1,
				handler: (f) => {
					const values = f.value;
					values.address = values.address+"@"+values.domain;
					delete values.domain;
					let a = this.entity.aliases;
					if(this.currentId) {
						const ca = a.find((m: any) => {return m.id == this.currentId});
						Object.assign(ca, values);
					} else {
						a.push(values);
					}
					jmapds("MailDomain").update(this.entity.id, {
						aliases: a
					}).then( () => {debugger;this.close();});
				}
			},
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
						required: true,
						readOnly: true,
						value: this.entity.domain,
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

		this.items.add(comp({cls: "scroll fit"},
			this.form,
			tbar({cls: "border-top"},
				"->",
				btn({cls: "filled primary", text: t("Save"), handler: (_btn) => {this.form.submit();}})
			)
			)
		);

	}
	public load(record: any) {
		record.domainId = this.entity!.id;
		if(record.id) {
			if(record.address.indexOf("@") > -1) {
				record.address = record.address.split("@")[0];
			}
			this.currentId = record.id;
		}
		this.form.value = record;
	}

}