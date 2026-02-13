import {
	appSystemSettings,
	client,
	JmapDataSource,
	modules,
} from "@intermesh/groupoffice-core";
import {BaseEntity, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "oauth2client",
	// entities: [
	// 	"DefaultClient",
	// 	"Oauth2Client",
	// ],
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:oauth2client"]) {
				return;
			}

			translate.load(GO.lang.community.oauth2client, "community", "oauth2client");
			if (session.isAdmin) {
				appSystemSettings.addPanel("community", "oauth2client", Settings);
			}

		});
	}
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