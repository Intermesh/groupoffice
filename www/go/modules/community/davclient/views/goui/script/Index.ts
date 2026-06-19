import {modules} from "@intermesh/groupoffice-core";
import {SystemSettings} from "./SystemSettings.js";

modules.register(  {
	package: "community",
	name: "davclient",
	entities: [
		"DavAccount"
	],
	settingsPanels: [SystemSettings]
});

