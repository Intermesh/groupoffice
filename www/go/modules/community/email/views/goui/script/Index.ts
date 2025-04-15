import {client, jmapds, modules} from "@intermesh/groupoffice-core";
import {datasourcestore, router, t, translate} from "@intermesh/goui";
import {Main} from "./Main";
export * from "./Main.js";

export const accountStore = datasourcestore({
	dataSource:jmapds('EmailAccount')
});

modules.register(  {
	package: "community",
	name: "email",
	entities: [
		"EmailAccount",
		"Identity",
		"Thread",
		"Mailbox",
		{
			name:"Email",
			filters: [
				{name: 'text', type: "string", multiple: false, title: t("Query")},
			],
			links: [{
				iconCls: 'entity ic-mail red',
				linkWindow:(entity:string, entityId) => {
					//todo
				},
				linkDetail:() =>  {
					//todo
				}
			}]
		}
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.email, "community", "email");

		client.on("authenticated",  (client, session) => {

			const ui = new Main(),
				nav = (accountId:string, mailboxId: string) => {
					modules.openMainPanel("email");
					ui.goto(accountId,mailboxId);
				};
			router.add(/^email\/a(\d+)\/m(\d+)$/, (accountId, mailboxId) => {
				nav(accountId, mailboxId);
			}).add(/^email\/a(\d+)$/, async (accountId) => {
				nav(accountId, "0");
			});

			modules.addMainPanel("community", "email", 'email', t('Email'), () => ui);

		});
	}
});