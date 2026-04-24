import {
	AclOwnerEntity,
	appSystemSettings,
	client,
	JmapDataSource,
	modules,
	moduleSettings
} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {UserAddressbookSettingsPanel} from "./UserAddressbookSettingsPanel.js";
import {UserProfileSettingsPanel} from "./UserProfileSettingsPanel.js";
import {BaseEntity, router, t, translate} from "@intermesh/goui";
import {Main} from "./Main.js";

export * from "./AddressBookCombo.js";
export * from "./ContactCombo.js";

translate.load(GO.lang.community.addressbook, "community", "addressbook");

modules.register({
	package: "community",
	name: "addressboook",
	entities: [
		"Contact",
		"AddressBook",
		"AddressBookGroup"
	],
	async init() {
		let addressbook: Main;
		client.on("authenticated", ({session}) => {
			if (session.isAdmin) {
				appSystemSettings.addPanel("community", "addressbook", Settings);
			}

			router.add(/^addressbook\/(\d+)$/, () => {
				modules.openMainPanel("addressbook");
			});

			router.add(/^contact\/(\d+)$/, (contactId) => {
				modules.openMainPanel("addressbook");
				addressbook.showContact(contactId);
			});

			modules.addMainPanel("community", "addressbook", "addressbook", t("Address book"), () => {
				addressbook = new Main();

				return addressbook;
			});

			moduleSettings.addPanel(UserAddressbookSettingsPanel);
			moduleSettings.addPanel(UserProfileSettingsPanel);
		});
	}
});


export interface AddressBook extends AclOwnerEntity {
	name: string;
	createdBy: string;
	filesFolderId: string | null | undefined;
	salutationTemplate: string;
	groups: string[];
}

export interface Contact extends BaseEntity {
	name?: string;
	jobTitle?: string;
	department?: string;
	gender?: 'M' | 'F' | null;
	organizationIds?: number[];
	isOrganization: boolean;
	phoneNumbers?: PhoneNumber[];
	emailAddresses?: EmailAddress[];
	addressBookId: string;
	addresses?: Address[];
	dates?: ContactDate[];
	urls?: ContactUrl[];
	prefixes?: string,
	suffixes?: string,
	photoBlobId?: string,
	color?: string,
	actionAt?: string;
	groups?: string[];
	starred?: boolean | null;
	vatNo?: string;
	IBAN?: string;
	registrationNumber?: string;
	debtorNumber?: string;
	notes?: string;
}

interface PhoneNumber {
	type: string;
	number: string;
}

interface EmailAddress {
	type: string;
	email: string;
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

export interface ContactUrl {
	type: string;
	url: string;
}

export const addressBookDS = new JmapDataSource<AddressBook>("AddressBook");
export const contactDS = new JmapDataSource<Contact>("Contact");
export const addressBookGroupDS = new JmapDataSource("AddressBookGroup");

export const typeStoreData = function (langKey: string) {
	const types = t(langKey, "community", "addressbook");

	return Object.entries(types).map(([value, name]) => ({
		value,
		name
	}));
}