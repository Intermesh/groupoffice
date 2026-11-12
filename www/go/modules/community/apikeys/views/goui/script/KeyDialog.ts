import {client, FormWindow, principalcombo} from "@intermesh/groupoffice-core";
import {fieldset, t, textfield} from "@intermesh/goui";

export class KeyDialog extends FormWindow {
	constructor() {
		super("Key");

		this.title = t("API key");
		this.stateId = "key-dialog";

		this.resizable = true;

		this.generalTab.items.add(
			fieldset({},
				textfield({
					name: "name",
					label: t("Name"),
					required: true
				}),
				principalcombo({
					entity: "User",
					name: "userId",
					label: t("User"),
					required: true,
					value: client.user.id
				})
			)
		)

		// The access token is only returned once by the server, when the key is created
		this.form.on("save", ({data, isNew}) => {
			if(isNew && data.accessToken) {
				browser.copyTextToClipboard(data.accessToken, true);
				void Window.alert(
					t("Copy the access token now. It's not possible to view it again.") + "<br><br><code>" + data.accessToken + "</code>",
					t("Access token")
				);
			}
		});
	}
}