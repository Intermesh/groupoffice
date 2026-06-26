import {JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";

modules.register({
	package: "community",
	name: "oidc",
	systemSettingsPanels: [Settings],
	entities: [
		{
			name: "OIDConnectClient"
		}
	]
})

export const OIDConnectClientDS = new JmapDataSource("OIDConnectClient");