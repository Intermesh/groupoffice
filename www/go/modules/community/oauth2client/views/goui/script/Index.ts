import {authManager, client, JmapDataSource, modules, moduleSystemSettings} from "@intermesh/groupoffice-core";
import {BaseEntity, btn, t} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "oauth2client",
	entities: [
		"DefaultClient",
		"Oauth2Client",
	],

	async init() {
		// The connections can only be changed by administrators, as in the server's Oauth2Client permissions
		client.on("authenticated", ({session}) => {
			if (session.isAdmin && session.capabilities["go:community:oauth2client"]) {
				moduleSystemSettings.addPanel("community", "oauth2client", Settings);
			}
		});
	}
});

export interface Oauth2Client extends BaseEntity {
	name: string,
	defaultClientId: string|undefined,
	/** Write only: it's always null when read */
	clientSecret?: string|null,
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



// The connections come with the capabilities, because the API can't be used before login. Only the ones for OpenID
// Connect are sent.
authManager.on("login", async ({loginWindow}) => {
	const data = await modules.getLoginData<{clients: {id: string, name: string, provider: string | null}[]}>("community", "oauth2client");

	data?.clients.forEach(c => {
		loginWindow.addSignInButton(btn({
			iconCls: c.provider ? `oauth2client-login-${c.provider}` : undefined,
			text: t("Sign in with {name}").replace("{name}", c.name),
			handler: () => {
				document.location = BaseHref + 'go/modules/community/oauth2client/gauth.php/openid/' + c.id;
			}
		}))
	});
})
