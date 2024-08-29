import {
	btn,
	checkbox,
	comp,
	DefaultEntity, EntityID,
	fieldset, Form,
	form, Notifier,
	t,
	tbar,
	textfield,
	Window
} from "@intermesh/goui";
import {client, jmapds} from "@intermesh/groupoffice-core";

export class MailboxExportDialog extends Window {
	private domainEntity: DefaultEntity|undefined;

	private form: Form|undefined

	constructor() {
		super();
		this.width = 640;
		this.resizable = false;
		this.closable = true;
		this.draggable = true;
		this.modal = true;
		this.maximizable = false;

		this.items.add(this.form = form({
			flex: 1,
			cls: "vbox",
			handler: (f) => {
				const v = f.value;
				let cols = ["username", "email", "name", "active"];
				if(v.resetPasswords) {
					cols.push("password");
				}
				let params = {
					ids: v.ids.split(","),
					extension: "csv",
					columns: cols
				};
				client.jmap("MailBox/export", params).then(async (response) => {
					debugger;
					if(response.blobId) {
						await client.downloadBlobId(response.blobId, response.blob.name);
					} else {
						Notifier.error(t("Error exporting mailboxes"), 5000);
					}
					this.close();
				});
			}
		},
			fieldset({flex: 1, cls: "border-top"},
				comp({html: t("If you enable reset passwords then a new password will be generated and " +
						"exported for each account. Warning! Existing client configuration will need to be updated " +
						"after this action.")}),
				checkbox({
					label: t("Reset passwords"),
					name: "resetPasswords",
					id: "resetPasswords",
					type: "switch"
				}),
				textfield({
					id: "ids",
					name: "ids",
					hidden: true
				})
			),
			tbar({
				cls: "border-top",
			}, "->", btn({type:"submit", text: t("Export") }))
		)
		);
	}

	public load(domainEntity: DefaultEntity, ids: Array<EntityID>) {
		this.domainEntity = domainEntity;
		this.title = t("Export domain:") + " " + this.domainEntity!.domain;
		this.form!.value = {ids: ids};
	}
}