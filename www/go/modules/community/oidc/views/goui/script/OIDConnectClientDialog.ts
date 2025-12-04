import {
	autocompletechips, browser, btn,
	checkbox, chips, column,
	comp, datasourcestore,
	DefaultEntity, fieldset, listStoreType, Notifier, storeRecordType,
	t, table, textarea, TextField,
	textfield
} from "@intermesh/goui";
import {client, FormWindow, jmapds} from "@intermesh/groupoffice-core";

export class OIDConnectClientDialog extends FormWindow {

	constructor() {
		super("OIDConnectClient");

		this.title = t("Client");
		this.maximizable = false;
		this.resizable = true;
		this.closable = true;
		this.width = 800;

		this.generalTab.items.add(
			fieldset({flex: 1},

				textfield({
					name: "name",
					id: "name",
					label: t("Name"),
					required: true
				}),
				textfield({
					name: "url",
					id: "url",
					label: t("Configuration URL"),
					hint: t("The Open ID endpoint that implements the .well-known/openid-configuration path")
				}),

				textfield({
					name: "clientId",
					id: "clientId",
					label: t("Client ID")
				}),

				textfield({
					type: "password",
					name: "clientSecret",
					id: "clientSecret",
					label: t("Client secret")
				}),

				textfield({
					label: "Redirect URI",
					readOnly: true,
					value: client.pageUrl("community/oidc/auth"),
					hint: t("Use this redirect URI in your provider's App registration"),
					buttons: [
						btn({
							icon: "content_copy",
							text: t("Copy to clipboard"),
							handler: button => {
								browser.copyTextToClipboard(client.pageUrl("community/oidc/auth"));
								Notifier.notice(t("Copied to clipboard"))
							}
						})
					]
				})
			),


		);
	}

}

