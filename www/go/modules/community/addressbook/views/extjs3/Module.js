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
			filters: [
				{
					name: 'q',
					type: "string",
					multiple: false,
					title: "Query"
				},
				{
					name: 'name',
					title: t("Name"),
					type: "string",
					multiple: true
				}, 
				{
					name: 'email',
					title: t("E-mail"),
					type: "string",
					multiple: true
				},{
					name: 'country',
					title: t("Country"),
					type: "string",
					multiple: true
				},{
					name: 'city',
					title: t("City"),
					type: "string",
					multiple: true
				},{
					name: 'gender',
					title: t("Gender"),
					type: "select",
					multiple: true,
					options: [{
							value: 'M',
							title: t("Male"),							
					},{
							value: 'F',
							title: t("Female")
					},{
							value: null,
							title: t("Unknown")
					}]
				},
				{
					title: t("Modified at"),
					name: 'modified', 
					multiple: false,
					type: 'date'
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
				}
			],
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
	}, "AddressBookGroup", {name: "ContactFilter"}],	
	
	userSettingsPanels: ["go.modules.community.addressbook.SettingsPanel"]
});

//go.Stores.get("User");