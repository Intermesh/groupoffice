import {authManager, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {SystemSettings} from "./SystemSettings.js";
import {btn, t} from "@intermesh/goui";

modules.register({
	package: "community",
	name: "oidc",
	systemSettingsPanels: [SystemSettings],
	entities: [
		{
			name: "OIDConnectClient"
		}
	]
})

export const OIDConnectClientDS = new JmapDataSource("OIDConnectClient");

// The clients come with the capabilities, because the API can't be used before login
authManager.on("login", async ({loginWindow}) => {
	const data = await modules.getLoginData<{clients: {id: string, name: string}[]}>("community", "oidc");

	data?.clients.forEach(c => {
		loginWindow.addSignInButton(btn({
			text: t("Sign in with {name}").replace("{name}", c.name),
			handler: () => {
				document.location = BaseHref + 'api/page.php/community/oidc/auth/' + c.id;
			}
		}))
	});
})
