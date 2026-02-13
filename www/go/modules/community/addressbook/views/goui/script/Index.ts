import {
	AclItemEntity,
	AclOwnerEntity,
	appSystemSettings,
	client,
	JmapDataSource,
	moduleSettings
} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {UserAddressbookSettingsPanel} from "./UserAddressbookSettingsPanel.js";
import {UserProfileSettingsPanel} from "./UserProfileSettingsPanel.js";
import {BaseEntity, translate} from "@intermesh/goui";

export * from "./AddressBookCombo.js";

translate.load(GO.lang.community.addressbook, "community", "addressbook");

export interface AddressBook extends AclOwnerEntity {
	name: string;
	createdBy: string;
	filesFolderId: string | null | undefined;
	salutationTemplate: string;
}

export interface Contact extends BaseEntity {
	name?: string;
	jobTitle?: string;
	department?: string;
	gender?: 'M' | 'F' | null;
	organizationIds?: number[];
	phoneNumbers?: PhoneNumber[];
	addresses?: Address[];
	dates?: ContactDate[];
	urls?: ContactUrl[];
}

interface PhoneNumber {
	type: string;
	number: string;
}

interface Address {
	type: string;
	address?: string;
	zipCode?: string;
	city?: string;
	state?: string;
	countryCode?: string;
}

interface ContactDate {
	type: string;
	date: string;
}

interface ContactUrl {
	type: string;
	url: string;
}

client.on("authenticated", ({session}) => {

	if (session.isAdmin) {
		appSystemSettings.addPanel("community", "addressbook", Settings);
	}

	moduleSettings.addPanel(UserAddressbookSettingsPanel);
	moduleSettings.addPanel(UserProfileSettingsPanel);
});

export const addressBookDS = new JmapDataSource<AddressBook>("AddressBook");