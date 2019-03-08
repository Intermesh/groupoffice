/* global go */

go.Modules.register("community", "addressbook", {
	mainPanel: "go.modules.community.addressbook.MainPanel",
	title: t("Address book"),
	entities: [{
			
			name: "Contact",
			hasFiles: true, //Rename to files?
			customFields: {
				fieldSetDialog: "go.modules.community.addressbook.CustomFieldSetDialog"
			},
			links: [{

				filter: "isContact",

				iconCls: "entity ic-person",

				/**
				 * Opens a dialog to create a new linked item
				 * 
				 * @param {string} entity eg. "Note"
				 * @param {string|int} entityId
				 * @returns {go.form.Dialog}
				 */
				linkWindow: function(entity, entityId) {
					return new go.modules.community.addressbook.ContactDialog();
				},

				/**
				 * Return component for the detail view
				 * 
				 * @returns {go.detail.Panel}
				 */
				linkDetail: function() {
					return new go.modules.community.addressbook.ContactDetail();
				}	
			},{
				/**
				 * Entity name
				 */
				title: t("Organization"),

				iconCls: "entity ic-business",			

				filter: "isOrganization",

				/**
				 * Opens a dialog to create a new linked item
				 * 
				 * @param {string} entity eg. "Note"
				 * @param {string|int} entityId
				 * @returns {go.form.Dialog}
				 */
				linkWindow: function(entity, entityId) {
					var dlg = new go.modules.community.addressbook.ContactDialog();
					dlg.setValues({isOrganization: true});
					return dlg;
				},

				/**
				 * Return component for the detail view
				 * 
				 * @returns {go.detail.Panel}
				 */
				linkDetail: function() {
					return new go.modules.community.addressbook.ContactDetail();
				}	
			}]
			
	}, {
		name: "AddressBook",
		title: t("Address book"),
		isAclOwner: true
	}, "AddressBookGroup"],
	
	systemSettingsPanels: ["go.modules.community.addressbook.SystemSettingsPanel"],
	userSettingsPanels: ["go.modules.community.addressbook.SettingsPanel"]
});

//go.Stores.get("User");