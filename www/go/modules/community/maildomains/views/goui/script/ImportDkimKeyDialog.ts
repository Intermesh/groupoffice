import {
	btn,
	checkbox,
	ContainerFieldValue,
	fieldset,
	Form,
	form,
	t, tbar,
	textarea,
	textfield,
	Window
} from "@intermesh/goui";
import {jmapds} from "@intermesh/groupoffice-core";
import {MailDomain} from "./MailDomain";

export class ImportDkimKeyDialog extends Window {
	readonly form: Form<ContainerFieldValue>;
	constructor(private domain:MailDomain) {
		super();

		this.title = t("DKIM key import")
		this.width = 600;
		this.height = 600;
		this.modal = true;

		this.items.add(
			this.form = form({
					flex:1,
					cls: "scroll",
					handler: async () => {
						this.mask();
						try {
							let dkim = structuredClone(this.domain.dkim);

							if(!dkim) {
								dkim = {};
							}
							const key = this.form.value;

							dkim[key.selector] = key;

							await jmapds("MailDomain").update(this.domain!.id, {dkim: dkim})
							this.close();
							return true;
						} catch(e) {
							void Window.error(e);
							return false;
						} finally {
							this.unmask();
						}
					}
				},
				fieldset({

				},

					checkbox({
						width: 200,
						name: "enabled",
						type: "switch",
						label: t("Enabled"),
						value: false
					}),

					textfield({
						required: true,
						label: t("Selector"),
						placeholder: "mail1",
						name: "selector"
					}),

					textarea({
						required: true,
						label: t("Private key") + "(PEM)",
						name: "privateKey",
						placeholder: "-----BEGIN PRIVATE KEY-----\n" +
							"...\n" +
							"-----END PRIVATE KEY-----"
					})
					)
			),

			tbar({},
				"->",
				btn({
				text: t("Save"),
				handler:async () => {
					this.form.submit();


				}
			}))
		)
	}
}