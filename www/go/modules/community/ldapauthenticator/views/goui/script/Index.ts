import {modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "ldapauthenticator",
	entities: [
		"LdapAuthServer"
	],
	systemSettingsPanels: [Settings]

});
