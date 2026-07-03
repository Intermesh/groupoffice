import {Account, main, modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";
import {fieldset, p, t} from "@intermesh/goui";


modules.register({
	package: "community",
	name: "otp",
	systemSettingsPanels: [Settings],
});

main.on("openusersettings", ({userSettingsWindow}) => {

	if(!modules.isAvailable("community", "otp")) {
		return;
	}


	const account = userSettingsWindow.findChildByType(Account)!;
	account.form!.items.add(fieldset({legend: t("Two Factor Authentication")}, p("TODO")));

})
