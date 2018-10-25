For old framework:

replace all GO.customfields.addColumns
advanced search
cascade delete
blocks module
logging in activerecord
duplicate
mergeWith
Search Cache
Export
Disabled categories for: ticket type, project template, calendar resource groups, folder dialog
modules/projects2/views/Extjs3/AddressbookOverrides.js

Refactor e-mail and document templates
Linking in email

calendar participants / address book
E-mail composer address book

For new:

Search Cache




CF:

Product
Factuur/Offerte
Afspraak
Calendar
Bestand
Map
TimeEntry
Ticket
Gebruiker


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
