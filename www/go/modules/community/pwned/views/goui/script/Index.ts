import {
	AclOwnerEntity,
	appSystemSettings,
	authSystemSettings,
	client,
	JmapDataSource,
	modules,
	router
} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "pwned",
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:pwned"]) {
				return;
			}

			translate.load(GO.lang.community.pwned, "community", "pwned");
			if (session.isAdmin) {
				authSystemSettings.addFieldset("community", "pwned", Settings);
			}

		});
	}
});
