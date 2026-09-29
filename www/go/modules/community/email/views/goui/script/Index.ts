import {client, jmapds, modules, router} from "@intermesh/groupoffice-core";
import {datasourcestore, store, t as coreT, translate} from "@intermesh/goui";
import {Main} from "./Main";
import {EmailView} from "./EmailView";
import {PreferencesPanel} from "./PreferencesPanel";
//export * from "./Main.js";

export const t = (key:string,p='community',m='email') => coreT(key, p,m);

export const emailAccountStore = datasourcestore({
	dataSource:jmapds('EmailAccount')
});

export const accountStore = store();

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

			accountStore.clear()
			for(const id in session.accounts) {
				const account = session.accounts[id];
				account.id = id;
				accountStore.add(account);
			}

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
			modules.addAccountSettingsPanel("community", "email", "email", t("Email"), "email", () => {
				return new PreferencesPanel();
			});
		});
	}
});