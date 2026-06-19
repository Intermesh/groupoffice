import {
	authSystemSettings,
	client,
	modules,
} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "imapauthenticator",
	entities: [
		"ImapAuthServer"
	],
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:imapauthenticator"]) {
				return;
			}

			if (session.isAdmin) {
				authSystemSettings.addFieldset("community", "imapauthenticator", Settings);
			}

		});
	}
});
