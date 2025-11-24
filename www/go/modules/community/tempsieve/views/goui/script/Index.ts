import {client, modules, router} from "@intermesh/groupoffice-core";
import {MainPanel} from "./MainPanel.js";
import {t, translate} from "@intermesh/goui";

modules.register(  {
	package: "community",
	name: "tempsieve",
	entities: ["Account"],
	async init () {
		client.on("authenticated",  ({session}) => {
			modules.getAll();

			if(!session.capabilities["go:community:tempsieve"]) {
				return;
			}

			translate.load(GO.lang.community.tempsieve, "community", "tempsieve");

			const mainPanel = new MainPanel();

			router.add(/^tempsieve\/(\d+)$/, (accountId) => {
				modules.openMainPanel("tempsieve");
				mainPanel.loadAccount(accountId);
			});

			router.add(/^tempsieve$/, () => {
				modules.openMainPanel("tempsieve");
				// mainPanel.setTaskId();
			});

			modules.addMainPanel( "community", "tempsieve", "tempsieve", t("TempSieve"), () => {
				return mainPanel;
			});
		});
	}
});


