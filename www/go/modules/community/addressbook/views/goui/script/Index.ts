import {AclOwnerEntity, appSystemSettings, client, JmapDataSource, modules} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
export * from "./AddressBookCombo.js"
export interface AddressBook extends AclOwnerEntity {
	name: string;
	createdBy: string;
	filesFolderId: string | null | undefined;
	salutationTemplate: string;
}

client.on("authenticated",  ({session}) => {

	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "addressbook", Settings);
	}
});

export const addressBookDS = new JmapDataSource<AddressBook>("AddressBook");