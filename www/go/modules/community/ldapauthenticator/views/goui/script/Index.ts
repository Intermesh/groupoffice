import {
	authSystemSettings,
	client,
	modules,
} from "@intermesh/groupoffice-core";
import {translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "ldapauthenticator",
	entities: [
		"LdapAuthServer"
	],
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:ldapauthenticator"]) {
				return;
			}

			translate.load(GO.lang.community.ldapauthenticator, "community", "ldapauthenticator");
			if (session.isAdmin) {
				authSystemSettings.addFieldset("community", "ldapauthenticator", Settings);
			}

		});
	}
});
