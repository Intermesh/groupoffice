import {Account, modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";
import {OtpSettingsFieldset} from "./OtpSettingsFieldset.js";


modules.register({
	package: "community",
	name: "otp",
	systemSettingsPanels: [Settings],
});

Account.patch(function() {
	this.form!.items.insert(-1, new OtpSettingsFieldset());
})
