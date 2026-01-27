import {AclOwnerEntity, appSystemSettings, client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "wopi",
	entities: [
		"WopiService",
	],
	async init() {
		client.on("authenticated", ( {session}) => {
			if (!session.capabilities["go:community:wopi"]) {
				return;
			}

			translate.load(GO.lang.community.wopi, "community", "wopi");
			if (session.isAdmin) {
				appSystemSettings.addPanel("community", "wopi", Settings);
			}

		});
	}
});

export interface WopiService extends AclOwnerEntity {
	name: string,
	type: string,
	url: string,
	wopiClientUri: string|undefined
}
export const WopiServiceDS = new JmapDataSource<WopiService>("WopiService");