import {checkbox, fieldset, numberfield, t, textfield, TextField} from "@intermesh/goui";
import {FormWindow} from "@intermesh/groupoffice-core";
import {AllowedModulesPanel} from "./AllowedModulesPanel.js";

export class InstanceDialog extends FormWindow {
	private hostnameField: TextField;
	private allowedModulesPanel: AllowedModulesPanel;

	constructor() {
		super("Instance");

		this.title = t("Instance");
		this.width = 600;
		this.height = 900;
		this.closeOnSave = false;

		this.generalTab.items.add(
			fieldset({},
				this.hostnameField = textfield({
					name: "hostname",
					label: t("Hostname"),
					required: true
				}),
				checkbox({
					type: "switch",
					name: "isTrial",
					label: t("This is a trial (will be deactivated automatically after 30 days)")
				}),
				numberfield({
					name: "storageQuota",
					label: t("Storage quota"),
					multiplier: 1 / (1024 * 1024 * 1024),
					hint: t("Size in GB")
				}),
				numberfield({
					name: "usersMax",
					decimals: 0,
					label: t("Maximum number of users")
				})
			)
		);

		this.allowedModulesPanel = new AllowedModulesPanel();
		this.allowedModulesPanel.disabled = true;
		this.cards.items.add(this.allowedModulesPanel);

		this.form.on("load", ({data}) => {
			this.hostnameField.disabled = true;
			this.allowedModulesPanel.setModules(data.allowedModules);
			this.allowedModulesPanel.disabled = false;
		});

		this.form.on("beforesave", ({data}) => {
			if (!this.allowedModulesPanel.disabled) {
				data.allowedModules = this.allowedModulesPanel.getAllowed();
			}
		});

		this.form.on("save", ({data, isNew}) => {
			if (isNew) {
				this.hostnameField.disabled = true;
				this.allowedModulesPanel.setModules(data.allowedModules);
				this.allowedModulesPanel.disabled = false;
				this.allowedModulesPanel.show();
			} else {
				this.close();
			}
		});
	}
}