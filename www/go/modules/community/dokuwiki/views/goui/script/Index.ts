import {appSystemSettings, client, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";

modules.register({
	package: "community",
	name: "dokuwiki",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:dokuwiki"]) {
				// User has no access to this module
				return;
			}
			if (session.isAdmin) {
				appSystemSettings.addPanel("community", "dokuwiki", Settings);
			}
		});
	}
});
