import {AclOwnerEntity, customFields, JmapDataSource, modules, GroupCombo} from "@intermesh/groupoffice-core";
import {Settings} from "./Settings.js";
import {UserAddressbookSettingsPanel} from "./UserAddressbookSettingsPanel.js";
import {UserProfileSettingsPanel} from "./UserProfileSettingsPanel.js";
import {BaseEntity, t, translate} from "@intermesh/goui";
import {ContactCustomField} from "./ContactCustomField.js";
import {MultiContactCustomField} from "./MultiContactCustomField.js";
import {AddressBookCombo} from "./AddressBookCombo.js";

export * from "./AddressBookCombo.js";
export * from "./ContactCombo.js";

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

export const addressBookDS = new JmapDataSource<AddressBook>("AddressBook");
export const contactDS = new JmapDataSource<Contact>("Contact");

customFields.registerType(new ContactCustomField());
customFields.registerType(new MultiContactCustomField());

modules.register({
	name:  "addressbook",
	package: "community",

	title: t("Addressbook"),
	mainPanel: "go.modules.community.addressbook.MainPanel",

	userSettingsPanels: [UserAddressbookSettingsPanel, UserProfileSettingsPanel],
	systemSettingsPanels: [Settings],

	/**
	 * Title of the module in start menu and main tabs
	 */
	// title: t("Address book"),

	/**
	 * Entities of the address book module
	 */
	entities: [{

		/**
		 * Client name it sends to the server
		 */
		name: "Contact",




		// /**
		//  * Override the custom field set dialog when creating it for a contact in system settings
		//  */
		customFields: {
			fieldSetDialog: "go.modules.community.addressbook.CustomFieldSetDialog"
		},
		/**
		 * Relations that can be fetched in stores and detail views
		 */
		relations: {
			organizations: {store: "Contact", fk: "organizationIds"},
			creator: {store: "Principal", fk: "createdBy"},
			modifier: {store: "Principal", fk: "modifiedBy"},
			addressbook: {store: "AddressBook", fk: "addressBookId"},
			test: {store: "AddressBook", fk: "testId"}
		},

		/**
		 * Filter definitions
		 *
		 * Will be used by query fields where you can use these like:
		 *
		 * name: Piet,John age: < 40
		 *
		 * Or when adding custom saved filters.
		 */
		filters: {
			text: {
				wildcards: false,
				type: "string",
				multiple: false,
				title: t("Query")
			},
			comment: {
				title: t("Comment"),
				multiple: true,
				type: 'string'
			},
			lastcontactat: {
				title: t("Last contact at"),
				multiple: false,
				type: 'date'
			},
			commentedat: {
				title: t("Commented at"),
				multiple: false,
				type: 'date'
			},
			modifiedat: {
				title: t("Modified at"),
				multiple: false,
				type: 'date'
			},
			modifiedBy: {
				title: t("Modified by"),
				multiple: true,
				type: 'string'
			},
			createdat: {
				title: t("Created at"),
				multiple: false,
				type: 'date'
			},
			createdby: {
				title: t("Created by"),
				multiple: true,
				type: 'string'
			},
			addressBookId: {
				title: t("Address book"),
				multiple: false,
				type: AddressBookCombo
			},
			isOrganization: {
				title: t('Type'),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Organization", 'addressbook', 'community')
					},
					{
						value: false,
						title: t("Contact", 'addressbook', 'community')
					}
				]
			},
			name: {
				title: t("Name"),
				type: "string",
				multiple: true
			},
			lastName: {
				title: t("Last name"),
				type: "string",
				multiple: true
			},
			firstName: {
				title: t("First name"),
				type: "string",
				multiple: true
			},
			notes: {
				title: t("Notes"),
				multiple: true,
				type: 'string'
			},
			email: {
				title: t("E-mail"),
				type: "string",
				multiple: true
			},
			phone: {
				title: t("Phone"),
				type: "string",
				multiple: true
			},
			country: {
				title: t("Country"),
				type: "string",
				multiple: true
			},
			zip: {
				title: t("ZIP code"),
				type: "string",
				multiple: true
			},
			city: {
				title: t("City"),
				type: "string",
				multiple: true
			},
			state: {
				title: t("State"),
				type: "string",
				multiple: true
			},
			address: {
				title: t("Address"),
				type: "string",
				multiple: true
			},
			street: { //deprecated. Alias for "address"
				title: t("Street"),
				type: "string",
				multiple: true
			},
			jobTitle: {
				title: t("Job title") + "/" + t("LOB"),
				type: "string",
				multiple: true
			},
			department: {
				title: t("Department"),
				type: "string",
				multiple: true
			},
			org: {
				title: t("Organization"),
				type: "string",
				multiple: true
			},
			orgCity: {
				title: t("Organization") + ": " + t("City"),
				type: "string",
				multiple: true
			},
			orgCountry: {
				title: t("Organization") + ": " + t("Country"),
				type: "string",
				multiple: true
			},
			gender: {
				title: t("Gender"),
				type: "select",
				multiple: true,
				options: [{
					value: 'M',
					title: t("Male"),
				}, {
					value: 'F',
					title: t("Female")
				}, {
					value: null,
					title: t("Unknown")
				}]
			},
			age: {
				title: t("Age"),
				multiple: false,
				type: 'number'
			},
			birthday: {
				title: t("Birthday"),
				multiple: false,
				type: 'date'
			},
			actiondate: {
				title: t("Action date"),
				multiple: false,
				type: 'date'
			},
			dateofbirth: {
				title: t("Date of birth"),
				multiple: false,
				type: 'date'
			},
			usergroupid: {
				title: t("User group"),
				multiple: true,
				type: GroupCombo
			},
			link: {
				title: t("Has links to..."),
				multiple: false,
				type: 'link'
			},
			isUser: {
				title: t("Is a user"),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Yes")
					},
					{
						value: false,
						title: t("No")
					}
				]
			},
			isInGroup: {
				title: t("Is in a group"),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Yes")
					},
					{
						value: false,
						title: t("No")
					}
				]
			},
			hasEmailAddresses: {
				title: t("Has e-mail addresses"),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Yes")
					},
					{
						value: false,
						title: t("No")
					}
				]
			},
			hasPhoneNumbers: {
				title: t("Has phone numbers"),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Yes")
					},
					{
						value: false,
						title: t("No")
					}
				]
			},
			hasOrganizations: {
				title: t("Has organizations"),
				multiple: false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Yes")
					},
					{
						value: false,
						title: t("No")
					}
				]
			},
			vatNo: {
				title: t("VAT number"),
				type: "string",
				multiple: true
			}
		},
		/**
		 * Link definitions
		 */
		links: [{

			/**
			 * Will filter only items that have isContact in the "filter" property.
			 * Contacts and Organizations are the same entity but have different link configurations
			 */
			filter: "isContact",

			/**
			 * Icon to show
			 */
			iconCls: "entity ic-person blue",

			/**
			 * Opens a dialog to create a new linked item
			 *
			 * @param {string} entity eg. "Note"
			 * @param {string|int} entityId
			 * @returns {go.form.Dialog}
			 */
			linkWindow: function (entity, entityId, data) {
				const dlg = new go.modules.community.addressbook.ContactDialog();
				const v:any = {
					isOrganization: false
				};

				if(data.addressBookId) {
					v.addressBookId = data.addressBookId;
				}
				dlg.setValues(v);
				return dlg;
			},

			/**
			 * Return component for the detail view
			 *
			 * @returns {go.detail.Panel}
			 */
			linkDetail: function () {
				return new go.modules.community.addressbook.ContactDetail();
			}
		}, {

			/**
			 * Title for the link detail panel, menu item etc.
			 */
			title: t("Organization"),

			iconCls: "entity ic-business purple",

			/**
			 * Will filter only items that have isContact in the "filter" property.
			 * Contacts and Organizations are the same entity but have different link configurations
			 */
			filter: "isOrganization",

			/**
			 * Opens a dialog to create a new linked item
			 *
			 * @param {string} entity eg. "Note"
			 * @param {string|int} entityId
			 * @returns {go.form.Dialog}
			 */
			linkWindow: function (entity, entityId, data) {
				const dlg = new go.modules.community.addressbook.ContactDialog();
				const v:any = {
					isOrganization: true
				};

				if(data.addressBookId) {
					v.addressBookId = data.addressBookId;
				}
				dlg.setValues(v);
				return dlg;
			},

			/**
			 * Return component for the detail view
			 *
			 * @returns {go.detail.Panel}
			 */
			linkDetail: function () {
				return new go.modules.community.addressbook.ContactDetail();
			}
		}]

	}, {
		name: "AddressBook",
		title: t("Address book")
	}, "AddressBookGroup"],

	/**
	 * Extra custom field types this module offers
	 */
	customFieldTypes: [
		"go.modules.community.addressbook.customfield.Contact",
		"go.modules.community.addressbook.customfield.MultiContact"
	],

	/**
	 * This panel will show in the select dialog for persons, e-mail addresses or phone numbers
	 *
	 * @see go.util.SelectDialog
	 */
	selectDialogPanels: [
		"go.modules.community.addressbook.SelectDialogPanel",
	],
})