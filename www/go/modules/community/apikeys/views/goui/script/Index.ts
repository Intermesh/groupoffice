import {client, modules} from "@intermesh/groupoffice-core";
import {t} from "@intermesh/goui";
import {SystemSettingsPanel} from "./SystemSettingsPanel.js";

modules.register({
	package: "community",
	name: "apikeys",
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:apikeys"]) {
				return;
			}

			modules.addSystemSettingsPanel("community", "apikeys", "apikeys", t("API Keys"), "lock", () => {
				return new SystemSettingsPanel();
			});
		})
	}
});


