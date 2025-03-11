import {checkbox, fieldset, numberfield, t, textfield} from "@intermesh/goui";
import {FormWindow, principalcombo} from "@intermesh/groupoffice-core";

export class DomainDialog extends FormWindow {


	constructor() {
		super("MailDomain");

		this.title = t("Domain");

		this.stateId = "maildomain-dialog";
		this.maximizable = true;
		this.resizable = true;
		this.closable = true;
		this.height = 800;
		this.width = 500;


		this.generalTab.items.add(
			fieldset({flex: 1},
				principalcombo({
					name: "userId",
					id: "userId",
					label: t("User"),
					required: true
				}),
				textfield({
					name: "domain",
					id: "domain",
					label: t("Domain"),
					required: true
				}),
				textfield({
					name: "description",
					id: "description",
					label: t("Description")
				}),
				textfield({
					name: "transport",
					id: "transport",
					value: "virtual",
					hidden: true,
					readOnly: true
				}),
				numberfield({
					name: "maxAliases",
					id: "maxAliases",
					label: t("Max aliases"),
					decimals: 0,
				}),
				numberfield({
					name: "maxMailboxes",
					id: "maxMailboxes",
					label: t("Max mailboxes"),
					decimals: 0,
				}),

				numberfield({
					name: "defaultQuota",
					id: "defaultQuota",
					label: t("Default quota (MB)"),
					multiplier: 1 / (1024 * 1024), // convert bytes to MB
					decimals: 0,
					required: true,
					value: 0
				}),

				numberfield({
					name: "totalQuota",
					id: "totalQuota",
					label: t("Max quota (MB)"),
					multiplier: 1 / (1024 * 1024), // convert bytes to MB
					decimals: 0,
					required: true,
					value: 0
				}),
				checkbox({
					label: t("Active"),
					name: "active",
					id: "active",
					type: "switch"
				}),
				checkbox({
					label: t("Backup MX"),
					name: "backupMx",
					id: "backupMx",
					type: "switch"
				})
			)
		);
		this.addSharePanel();

		this.on("ready", async () => {
			if (this.currentId) {

				this.form.findField("domain")!.disabled = true;

			}
		});

	}




}