import {appSystemSettings, client, modules} from "@intermesh/groupoffice-core";
import {t} from "@intermesh/goui";
import {SystemSettingsPanel} from "./SystemSettingsPanel.js";
import {Settings} from "./Settings.js";

modules.register({
	package: "community",
	name: "apikeys"
});

client.on("authenticated",  ({session}) => {
	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "apikeys", Settings);
	}
});

