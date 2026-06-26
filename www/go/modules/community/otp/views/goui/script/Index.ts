import {modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "otp",
	systemSettingsPanels: [Settings],
});
