import {FormWindow} from "@intermesh/groupoffice-core";
import {checkbox, combobox, fieldset, t, TextField, textfield} from "@intermesh/goui";
import {DefaultClientDS} from "./Index";

export class Oauth2ClientDialog extends FormWindow {

	constructor() {
		super("Oauth2Client");
		this.title = t("OAuth2 Connection");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;
		this.height = 800;

		let secretFld: TextField;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				combobox({
					label: t("Provider"),
					dataSource: DefaultClientDS,
					name: "defaultClientId",
					required: true
				}),
				textfield({
					name: "clientId",
					label: t("Client Id"),
					required: true
				}),
				secretFld = textfield({
					type: "password",
					name: "clientSecret",
					label: t("Client Secret"),
					autocomplete: "new-password",
					required: true
				}),
				textfield({
					name: "projectId",
					label: t("API Project Id"),
					required: true,
					hint: t("Enter the API Project Id if you use Google or the Tenant Id if you use Microsoft")
				}),
				checkbox({
					type: "switch",
					name: "openId",
					label: t("Use this connection for single signon with OpenID Connect")
				})
			)
		);

		// The secret is write only, so it's never loaded. When editing, leaving it empty keeps the current secret.
		this.form.on("load", () => {
			secretFld.required = false;
			secretFld.hint = t("Leave empty to keep the current secret");
		});
	}
}
