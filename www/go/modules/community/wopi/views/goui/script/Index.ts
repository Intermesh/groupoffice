import {AclOwnerEntity, appSystemSettings, client, JmapDataSource, modules, router} from "@intermesh/groupoffice-core";
import {t, translate} from "@intermesh/goui";
import {Settings} from "./Settings";


modules.register({
	package: "community",
	name: "wopi",
	entities: [
		"WopiService",
	],
	systemSettingsPanels: [Settings]
});

export interface WopiService extends AclOwnerEntity {
	name: string,
	type: string,
	url: string,
	wopiClientUri: string|undefined
}
export const WopiServiceDS = new JmapDataSource<WopiService>("WopiService");