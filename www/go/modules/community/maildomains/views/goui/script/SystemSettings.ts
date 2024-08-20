import {
	Component,
	containerfield,
	ContainerFieldValue,
	datasourceform,
	fieldset,
	Form,
	form,
	t,
	textfield
} from "@intermesh/goui";
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
					textfield({
						label: t("Mail hostname"),
						name: "mailHost",
						hint: t("The hostname of the mail system. Used for PTR, SPF and MX checks.")
					})

				)
			)
		)

		this.items.add(this.form);

		const mod = modules.get("community", "maildomains");

		if(mod)
			this.form.load(mod.id);
	}
	onSubmit() {
		debugger;
		return this.form.submit();
	}
}