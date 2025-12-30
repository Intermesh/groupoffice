import {client, modules, router} from "@intermesh/groupoffice-core";
import {MainPanel} from "./MainPanel.js";
import {
	DefaultEntity,
	t,
	translate
} from "@intermesh/goui";

export interface SieveRuleEntity {
	name?: string,
	index: number,
	idx?: number,
	scriptName?: string,
	active: boolean,
	join?: "any"|"anyof"|"allof",
	raw: string
}

export interface SieveScriptEntity {
	name?: string,
	script_name?: string,
	script: string,
	active: boolean
}

export interface SieveCriteriumEntity {
	id?: string,
	index?: number,
	test: string,
	not: boolean,
	type?: string,
	arg?: string,
	arg1?: string,
	arg2?: string,
	part?: string
}
export interface SieveActionEntity {
	id?: string,
	type: string,
	// copy: string,
	text: string,
	target?: string,
	days?: string,
	addresses?: string,
	reason?: string,
	subject?: string|null,
	copy?: boolean
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


