import {main, modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";
import {p} from "@intermesh/goui";


modules.register({
	package: "community",
	name: "otp",
	systemSettingsPanels: [Settings],
});
