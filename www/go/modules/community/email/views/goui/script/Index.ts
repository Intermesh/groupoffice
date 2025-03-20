import {client, modules} from "@intermesh/groupoffice-core";
import {router, t, translate} from "@intermesh/goui";
import {Main} from "./Main";
export * from "./Main.js";


modules.register(  {
	package: "community",
	name: "email",
	entities: [
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
				nav = (accountId:string, emailId: string) => {
					modules.openMainPanel("email");
					ui.goto(accountId,emailId);
				};
			router.add(/^email\/a(\d+)\/e(\d+)$/, (accountId, emailId) => {
				nav(accountId, emailId);
			}).add(/^email\/a(\d+)$/, async (accountId) => {
				nav(accountId, "0");
			});

			modules.addMainPanel("community", "email", 'email', t('Email'), () => ui);

		});
	}
});