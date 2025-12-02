import {
	autocompletechips,
	checkbox, chips, column,
	comp, datasourcestore,
	DefaultEntity, fieldset, listStoreType, storeRecordType,
	t, table, textarea, TextField,
	textfield
} from "@intermesh/goui";
import {FormWindow, jmapds} from "@intermesh/groupoffice-core";

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
			),


		);
	}

}

