import {modules} from "@intermesh/groupoffice-core";
import {MainPanel} from "./MainPanel.js";
import {t} from "@intermesh/goui";
import {Settings} from "./Settings.js";

modules.register(  {
	package: "community",
	name: "maildomains",
	entities: [
		"MailDomain",
		"MailAlias",
		"MailBox"
	],
	panels: {
		maildomains: {
			title: t("Mail domains"),
			cmp: MainPanel,
			routes:{
				"^maildomains\/(\d+)$"(domainId) {
					this.show();
					this.setDomainId(domainId)
				}
			}
		}
	},
	systemSettingsPanels: [Settings]
});