/* global go */

go.Modules.register("community", "addressbook", {
	/**
	 * Main panel is added to the UI when clicking address book in the start menu
	 */
	mainPanel: "go.modules.community.addressbook.MainPanel",

	/**
	 * Title of the module in start menu and main tabs
	 */
	title: t("Address book"),

	/**
	 * Entities of the address book module
	 */
	entities: [{

		/**
		 * Client name it sends to the server
		 */
		name: "Contact",

		/**
		 * Override the custom field set dialog when creating it for a contact in system settings
		 */
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
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: t("Query")
			},
			{
				title: t("Comment"),
				name: 'comment',
				multiple: true,
				type: 'string'
			},
			{
				title: t("Last contact at"),
				name: 'lastcontactat',
				multiple: false,
				type: 'date'
			},
			{
				title: t("Commented at"),
				name: 'commentedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified at"),
				name: 'modifiedat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Modified by"),
				name: 'modifiedBy',
				multiple: true,
				type: 'string'
			}, {
				title: t("Created at"),
				name: 'createdat',
				multiple: false,
				type: 'date'
			}, {
				title: t("Created by"),
				name: 'createdby',
				multiple: true,
				type: 'string'
			},
			{
				title: t("Address book"),
				name: 'addressBookId',
				multiple: false,
				type: "go.modules.community.addressbook.AddresBookCombo"
			},{
				title: t('Type'),
				name:'isOrganization',
				multiple:false,
				type: 'select',
				options: [
					{
						value: true,
						title: t("Organization",'addressbook', 'community')
					},
					{
						value: false,
						title: t("Contact",'addressbook', 'community')
					}
				]
			},
			{
				name: 'name',
				title: t("Name"),
				type: "string",
				multiple: true
			},
			{
				name: 'lastName',
				title: t("Last name"),
				type: "string",
				multiple: true
			},

			{
				name: 'firstName',
				title: t("First name"),
				type: "string",
				multiple: true
			},
			{
				title: t("Notes"),
				name: 'notes',
				multiple: true,
				type: 'string'
			},

			{
				name: 'email',
				title: t("E-mail"),
				type: "string",
				multiple: true
			}, {
				name: 'phone',
				title: t("Phone"),
				type: "string",
				multiple: true
			}, {
				name: 'country',
				title: t("Country"),
				type: "string",
				multiple: true
			}, {
				name: 'zip',
				title: t("ZIP code"),
				type: "string",
				multiple: true
			}, {
				name: 'city',
				title: t("City"),
				type: "string",
				multiple: true
			}, {
				name: 'state',
				title: t("State"),
				type: "string",
				multiple: true
			}, {
				name: 'address',
				title: t("Address"),
				type: "string",
				multiple: true
			}, {
				name: 'street', //deprecated. Alias for "address"
				title: t("Street"),
				type: "string",
				multiple: true
			},  {
				name: 'jobTitle',
				title: t("Job title") + "/" +  t("LOB"),
				type: "string",
				multiple: true
			},  {
				name: 'department',
				title: t("Department"),
				type: "string",
				multiple: true
			}, {
				name: 'org',
				title: t("Organization"),
				type: "string",
				multiple: true
			}, {
				name: 'orgCity',
				title: t("Organization") + ": " + t ("City"),
				type: "string",
				multiple: true
			}, {
				name: 'orgCountry',
				title: t("Organization") + ": " + t ("Country"),
				type: "string",
				multiple: true
			}, {
				name: 'gender',
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
			{
				title: t("Age"),
				name: 'age',
				multiple: false,
				type: 'number'
			},
			{
				title: t("Birthday"),
				name: 'birthday',
				multiple: false,
				type: 'date'
			},{
				title: t("Action date"),
				name: 'actiondate',
				multiple: false,
				type: 'date'
			},{
				title: t("Date of birth"),
				name: 'dateofbirth',
				multiple: false,
				type: 'date'
			}, {
				title: t("User group"),
				name: 'usergroupid',
				multiple: true,
				type: 'go.groups.GroupCombo'
			}, {
				title: t("Has links to..."),
				name: 'link',
				multiple: false,
				type: 'go.links.FilterLinkEntityCombo'
			}, {
				title: t("Is a user"),
				name: 'isUser',
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
			}, {
				title: t("Is in a group"),
				name: 'isInGroup',
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
			}, {
				title: t("Has e-mail addresses"),
				name: 'hasEmailAddresses',
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
			}, {
				title: t("Has phone numbers"),
				name: 'hasPhoneNumbers',
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
			},  {
				title: t("Has organizations"),
				name: 'hasOrganizations',
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
			},{
				title: t("VAT number"),
				name: 'vatNo',
				type:"text",
				multiple: true
			}
		],

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
				const v = {
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
				const v = {
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
	 * These panels will show in the My Account or user settings dialog
	 */
	userSettingsPanels: [
		"go.modules.community.addressbook.SettingsPanel",
		"go.modules.community.addressbook.SettingsProfilePanel"
	],

	/**
	 * These panels will show in the System settings
	 */
	systemSettingsPanels: [
		"go.modules.community.addressbook.SystemSettingsPanel",
	],


	/**
	 * This panel will show in the select dialog for persons, e-mail addresses or phone numbers
	 *
	 * @see go.util.SelectDialog
	 */
	selectDialogPanels: [
		"go.modules.community.addressbook.SelectDialogPanel",
	],

	/**
	 * Extra custom field types this module offers
	 */
	customFieldTypes: [
		"go.modules.community.addressbook.customfield.Contact",
		"go.modules.community.addressbook.customfield.MultiContact"
	]
});


go.modules.community.addressbook.typeStoreData = function (langKey) {
	var types = [], typeLang = t(langKey);

	for (var key in typeLang) {
		types.push([key, typeLang[key]]);
	}
	return types;
};

go.modules.community.addressbook.importVcf = function(config) {
	Ext.MessageBox.confirm(t('Confirm'), t('Are you sure that you would like to import this VCard?'),
		function(btn) {
			if (btn !== "yes") {
				return;
			}
			Ext.getBody().mask(t("Importing..."));
			go.Jmap.request({
				method: "Contact/loadVCF",
				params: {
					fileId: config.id
					// account_id: panel.account_id,
					// mailbox: panel.mailbox,
					// uid: panel.uid,
					// number: attachment.number,
					// encoding: attachment.encoding
				},
				callback: function (options, success, response) {
					Ext.getBody().unmask();
					if (!success) {
						Ext.MessageBox.alert(t("Error"), response.errors.join("<br />"));
					} else {
						var dlg = new go.modules.community.addressbook.ContactDialog();
						dlg.load(response.contactId).show();
					}
				}
			});
		});
}

go.modules.community.addressbook.renderName = function(contact) {
	const sortBy = go.User.addressBookSettings.sortBy;
	let name;
	if(!contact.isOrganization && sortBy == 'lastName' && !go.util.empty(contact.lastName)) {
		name = contact.lastName + ', ' + contact.firstName;
		if(!go.util.empty(contact.middleName)) {
			name += " " + contact.middleName;
		}
	} else{
		name = contact.name;
	}

	return name;
};


go.modules.community.addressbook.lookUpUserContact = async (userId) => {
	//lookup in address book
	const ids = await go.Db.store("Contact").query({
		filter: {
			isUser: userId
		}
	}).then(r=>r.ids);

	if(!ids.length) {
		Ext.MessageBox.alert(t("Not found"), t("Could not find this user in the address book for you."));
	} else
	{
		go.Entities.get("Contact").goto(ids[0]);
	}
};

Ext.onReady(function () {
	if (!go.modules.business || !go.modules.business.newsletters) {
		return;
	}

	go.modules.business.newsletters.registerEntity({
		name: "Contact",
		grid: go.modules.community.addressbook.ContactGrid,
		add: function () {
			return new Promise(function (resolve, reject) {
				var select = new go.util.SelectDialog({
					entities: ['Contact'],
					mode: 'id',
					scope: this,
					selectMultiple: function (ids) {
						this.resolved = true;
						resolve(ids);
					},
					listeners: {
						close: function () {
							if (!this.resolved) {
								reject();
							}
						}
					}
				});
				select.show();
			});
		}
	});
});
