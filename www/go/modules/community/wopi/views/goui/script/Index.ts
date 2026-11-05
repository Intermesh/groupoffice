import {AclOwnerEntity, JmapDataSource, modules,} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings";
import {t} from "@intermesh/goui";


modules.register({
	package: "community",
	name: "wopi",
	entities: [
		{
			name: "WopiService",
			permissions: [
				{value: 10, name: t("Read")},
				{value: 30, name: t("Write")}
			]
		},
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