import {
	authSystemSettings,
	client,
	modules,
} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "otp",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities["go:community:otp"]) {
				return;
			}

			translate.load(GO.lang.community.otp, "community", "otp");
			if (session.isAdmin) {
				authSystemSettings.addFieldset("community", "otp", Settings);
			}

		});
	}
});
