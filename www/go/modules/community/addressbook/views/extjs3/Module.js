/* global go */

go.Modules.register("community", "addressbook", {
	mainPanel: "go.modules.community.addressbook.MainPanel",
	title: t("Addressbook"),
	entities: [{
			/**
			 * Entity name
			 */
			name: "Contact",
			
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
			 * @returns {go.panels.DetailView}
			 */
			linkDetail: function() {
				return new go.modules.community.addressbook.ContactDetail();
			}	
		}, "AddressBook", "AddressBookGroup"],
	systemSettingsPanels: ["go.modules.community.addressbook.SystemSettingsPanel"],
	initModule: function () {}
});

//go.Stores.get("User");