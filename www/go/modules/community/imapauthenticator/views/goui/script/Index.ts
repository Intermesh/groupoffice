import {
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
	systemSettingsPanels: [Settings]
});
