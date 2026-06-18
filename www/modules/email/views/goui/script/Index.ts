import {BaseEntity, t} from "@intermesh/goui";
import {UserSettingsPanel} from "./UserSettingsPanel.js";
import {client, extjswrapper, JmapDataSource, modules, moduleSettings} from "@intermesh/groupoffice-core";

modules.register({
	package: "legacy",
	name: "email",
	async init() {
		client.on("authenticated", ({session}) => {
			if (!session.capabilities['go:legacy:email']) {
				return;
			}

			moduleSettings.addPanel(UserSettingsPanel);
			// const id = (m.package ?? "legacy") + "/" + m.moduleName;
			//
			// this.mainPanels[id] = {
			// 	package: m.package,
			// 	module: m.moduleName,
			// 	id: id,
			// 	title: m.title,
			// 	callback: () => {
			//
			// 		const pnl = GO.moduleManager.getPanel(m.moduleName);
			// 		pnl.header = false;
			//
			// 		return extjswrapper({
			// 			cls: "fit",
			// 			title: m.title,
			// 			comp: Ext.create(pnl)
			// 		});
			// 	}
			// };


		});
	}
});

export interface Template extends BaseEntity {
	id: string,
	name: string,
	user_id: string
}

export const templateDS = new JmapDataSource<Template>("Template");