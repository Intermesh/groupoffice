import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {SystemSettings} from "./SystemSettings.js";

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