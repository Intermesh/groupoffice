import {client, jmapds, modules, router} from "@intermesh/groupoffice-core";
import {datasourcestore, t, translate} from "@intermesh/goui";
import {Main} from "./Main";
import {EmailView} from "./EmailView";
//export * from "./Main.js";

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
					return new EmailView();
				}
			}]
		}
	],
	init () {
		//const user = client.user;
		translate.load(GO.lang.community.email, "community", "email");

		client.on("authenticated",  ({session}) => {

			const ui = new Main(),
				nav = (accountId:string, mailboxId: string, threadId: string = '') => {
					modules.openMainPanel("email");
					// client.jmap('Thread/get',{
					// 	'#ids': {resultOf: 'c1', name: 'Email/get', path: '/list/*/threadId'}
					// })
					// client.jmap('Email/get',{
					// 	'#ids': {resultOf: 'r'+($dw.jmap.reqCount-1), name: 'Thread/get', path: '/list/*/emailIds'},
					// 	properties: ["threadId", "mailboxIds", "keywords", "hasAttachment", "from", "subject", "receivedAt", "size", "preview"]
					// }).then((response) => {
					// 	todo('update', {ids:response.ids});
					// })
					ui.goto(accountId,mailboxId, threadId);
				};
			router.add(/^email\/a(\d+)\/m(\d+)(?:\/t(\d+))?$/, (accountId, mailboxId, threadId?: string) => {
				nav(accountId, mailboxId, threadId);
			}).add(/^email\/a(\d+)$/, async (accountId) => {
				nav(accountId, "0");
			});

			modules.addMainPanel("community", "email", 'email', t('Email'), () => ui);

		});
	}
});