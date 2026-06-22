import {AclOwnerEntity, JmapDataSource, modules,} from "@intermesh/groupoffice-core";
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