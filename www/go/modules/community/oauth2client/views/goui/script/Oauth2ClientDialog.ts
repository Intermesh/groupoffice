import {FormWindow} from "@intermesh/groupoffice-core";
import {checkbox, combobox, fieldset, t, textfield} from "@intermesh/goui";
import {DefaultClientDS} from "@intermesh/community/oauth2client";

export class Oauth2ClientDialog extends FormWindow {

	constructor() {
		super("Oauth2Client");
		this.title = t("OAuth2 Connection");

		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;
		this.height = 800;

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
				textfield({
					name: "clientSecret",
					label: t("Client Secret"),
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
	}
}