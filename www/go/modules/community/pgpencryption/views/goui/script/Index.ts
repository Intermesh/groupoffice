import {Entity, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {UserSettingsPanel} from "./UserSettingsPanel.js";

modules.register({
	name: "pgpencryption",
	package: "community",
	userSettingsPanels: [UserSettingsPanel],
	entities: [
		"PublicKey",
		"PrivateKey"
	]
});

interface PublicPGPKey extends Entity {
	id: string,
	key: string,
	createdAt?: string,
	userId: string,
	email: string
}

export const publicPGPKeyDS = new JmapDataSource<PublicPGPKey>("PublicKey");
export const privatePGPKeyDS = new JmapDataSource("PrivateKey");

// placeholder until addressbook in goui is released
export const contactDS = new JmapDataSource("Contact");