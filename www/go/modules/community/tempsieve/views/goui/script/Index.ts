import {client, modules, router} from "@intermesh/groupoffice-core";
import {MainPanel} from "./MainPanel.js";
import {
	DefaultEntity,
	t,
	translate
} from "@intermesh/goui";

export interface SieveRuleEntity extends DefaultEntity {
	name: string,
	index: number,
	script_name: string,
	active: boolean
}

export interface SieveScriptEntity extends DefaultEntity {
	name: string,
	script_name: string,
	active: boolean
}

export interface SieveCriteriumEntity extends DefaultEntity {
	test: string,
	not: string,
	type: string,
	arg1: string,
	arg2: string
}

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
			});

			modules.addMainPanel( "community", "tempsieve", "tempsieve", t("TempSieve"), () => {
				return mainPanel;
			});
		});
	}
});


