import {modules} from "@intermesh/groupoffice-core";
import {SystemSettingsPanel} from "./SystemSettingsPanel.js";

modules.register({
	package: "community",
	name: "apikeys",
	entities: ['Key'],
	systemSettingsPanels: [SystemSettingsPanel]
});


