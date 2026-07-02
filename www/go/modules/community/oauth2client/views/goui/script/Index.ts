import {authManager, JmapDataSource, modules,} from "@intermesh/groupoffice-core";
import {BaseEntity, btn, t} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "oauth2client",
	entities: [
		"DefaultClient",
		"Oauth2Client",
	],

	systemSettingsPanels: [Settings]

});

export interface Oauth2Client extends BaseEntity {
	name: string,
	defaultClientId: string|undefined,
	clientSecret: string,
	clientId: string,
	projectId: string|undefined
}
export interface DefaultClient extends BaseEntity {
	name: string,
	authenticationMethod: string,
	imapHost: string,
	imapPort: number,
	imapEncryption: string|undefined,
	smtpHost: string,
	smtpPort: number,
	smtpEncryption: string|undefined,
}
export const Oauth2ClientDS = new JmapDataSource<Oauth2Client>("Oauth2Client");
export const DefaultClientDS = new JmapDataSource<DefaultClient>("DefaultClient");



authManager.on("login", async ({loginWindow}) => {

	loginWindow.mask();
	try {
		const clientQuery = await Oauth2ClientDS.query({
			sort: [{property: "name"}]
		});

		const clients = await Oauth2ClientDS.get(clientQuery.ids);

		for (const c of clients.list) {

			const defClient = await DefaultClientDS.single(c.defaultClientId!);

			loginWindow.addSignInButton(btn({
				iconCls: `oauth2client-login-${defClient.name}`,
				text: t("Sign in with {name}").replace("{name}", c.name),
				handler: () => {
					document.location = BaseHref + 'go/modules/community/oauth2client/gauth.php/openid/' + c.id;
				}
			}))
		}
	}finally {
		loginWindow.unmask();
	}

})