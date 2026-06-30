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

authManager.on("login", async ({loginWindow}) => {

	loginWindow.mask();
	try {
		const clientQuery = await OIDConnectClientDS.query({
			sort: [{property: "name"}]
		});

		const clients = await OIDConnectClientDS.get(clientQuery.ids);

		clients.list.forEach(c => {
			loginWindow.addSignInButton(btn({
				text: t("Sign in with {name}").replace("{name}", c.name),
				handler: () => {
					document.location = BaseHref + 'api/page.php/community/oidc/auth/' + c.id;
				}
			}))
		})
	}finally {
		loginWindow.unmask();
	}

})