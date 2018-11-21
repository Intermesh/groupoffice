For old framework:

advanced search
cascade delete custom fields

logging in activerecord
duplicate
mergeWith
Search Cache
Export contacts VCF, CSV
Disabled categories for: ticket type, calendar resource groups, folder dialog
modules/projects2/views/Extjs3/AddressbookOverrides.js
FilesModule::afterBatchEditStore
Folder:: deriveCustomfieldSettings
Address lists

Refactor e-mail and document templates


Contact -> Email compose vanuit dropdown

For new:

	Search Cache custom fields
	Attachments in notes

	calendar birthdays
	demodata
	carddav
	zpush
	site / defaultsite
	ticket groups
	templates for project contracts (See income model)


Custom field update / install queries for modules
Migration of templates, custom fields and address book


Affected customer modules:
favorites (greifswald)
  blocks module
	bestgroen
	relations
	AMD
	calendarcompany
	comments report (Houtwerf?)
	disableformfields (Elite)
	efront
	elite
	employee gallery
	exactonline (dg)
	fivehundredwords
	forms ?
	kassanova
	maranga
	nuwbackup
	nuwleadreport
	orderplanning (weap)
	radius search (nltechniek)
	reservations
	sendletter
	unit4export
	voip
	werkplaatsplanning (WEAP)
	xero







CF:

TimeEntry
Gebruiker
Site content en site


PHP Model:

use \go\core\orm\CustomFieldsTrait;

Detail panel:

this.add(go.modules.core.customfields.CustomFields.getDetailPanels("Task"));

Store fields:

.concat(go.modules.core.customfields.CustomFields.getFieldDefinitions("Task"))

Grid columns:

.concat(go.modules.core.customfields.CustomFields.getColumns("Task"))


Dialog:

propertiesPanel.add(go.modules.core.customfields.CustomFields.getFormFieldSets("Task"));


System settings
GO.tasks.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-done',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Tasks");		
		
		this.items = [new go.modules.core.customfields.SystemSettingsPanel({
				entity: "Task"
//				createFieldSetDialog : function() {
//					return new go.modules.community.addressbook.CustomFieldSetDialog();
//				}
		})];
		
		
		GO.tasks.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
