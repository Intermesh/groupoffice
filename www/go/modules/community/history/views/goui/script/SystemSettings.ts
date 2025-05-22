import {Component, containerfield, datasourceform, datasourcestore, fieldset, numberfield, t} from "@intermesh/goui";
import {jmapds, modules} from "@intermesh/groupoffice-core";

export class SystemSettings extends Component {
	private form;

	constructor() {
		super();

		this.form = datasourceform({
				dataSource: jmapds("Module")
			},
			fieldset({},

				containerfield({
						name: "settings"
					},
					numberfield({
						id: "deleteAfterDays",
						label: t("Delete entries after how many days")
					})
				)
			)
		)

		this.items.add(this.form);

		const mod = modules.get("community", "history");

		if(mod)
			this.form.load(mod.id);
	}

	onSubmit() {
		return this.form.submit();
	}
}