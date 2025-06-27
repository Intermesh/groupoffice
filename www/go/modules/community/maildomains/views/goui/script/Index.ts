import {client, modules, router} from "@intermesh/groupoffice-core";
import {MainPanel} from "./MainPanel.js";
import {comp, t, translate} from "@intermesh/goui";
import {SystemSettings} from "./SystemSettings.js";

modules.register(  {
	package: "community",
	name: "maildomains",
	async init () {
		client.on("authenticated",  ({session}) => {
			if(!session.capabilities["go:community:maildomains"]) {
				// User has no access to this module
				return;
			}

			translate.load(GO.lang.core.core, "core", "core");
			translate.load(GO.lang.community.maildomains, "community", "maildomains");

			const mainPanel = new MainPanel();

			router.add(/^maildomains\/(\d+)$/, (domainId) => {
				modules.openMainPanel("maildomains");

				mainPanel.setDomainId(domainId);
			});

			router.add(/^maildomains$/, () => {
				modules.openMainPanel("maildomains");
			});

			modules.addMainPanel( "community", "maildomains", "maildomains", t("Mail domains"), () => {
				//this will lazy load Notes when module panel is opened.
				return mainPanel;
			});

			modules.addSystemSettingsPanel("community", "maildomains", "maildomains", t("Mail domains"), "email", () => {
				return new SystemSettings();
			});
		});
	}
});