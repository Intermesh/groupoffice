import {BaseEntity} from "@intermesh/goui";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {client, JmapDataSource, modules, moduleSettings} from "@intermesh/groupoffice-core";

modules.register({
	package: "legacy",
	name: "email",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities['go:legacy:email']) {
				return;
			}

			moduleSettings.addPanel(UserSettingsPanel);
		});
	}
});

export interface Template extends BaseEntity {
	id: string,
	name: string,
	user_id: string
}

export const templateDS = new JmapDataSource<Template>("Template");