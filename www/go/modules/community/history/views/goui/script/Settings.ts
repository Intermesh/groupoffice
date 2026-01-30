import {jmapds} from "@intermesh/groupoffice-core";
import {containerfield, Fieldset, numberfield, t} from "@intermesh/goui";

export class Settings extends Fieldset {
	constructor() {
		super();
		this.legend = t("Settings");
		this.items.add(
			containerfield({
					name: "settings"
				},
				numberfield({
					id: "deleteAfterDays",
					decimals: 0,
					name: 'deleteAfterDays',
					label: t("Retention period of entries in days", "community", "history"),
					hint: `${t("Delete entries after", "community", "history")} X ${t("Days").toLowerCase()}`
				})
			)
		);
	}
}