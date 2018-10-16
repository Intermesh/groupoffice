/* global go, Ext */

go.modules.community.notes.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-notes',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Notes");		
		
		this.items = [new go.modules.core.customfields.SystemSettingsPanel({
				entity: "Note"
//				createFieldSetDialog : function() {
//					return new go.modules.community.addressbook.CustomFieldSetDialog();
//				}
		})];
		
		
		go.modules.community.notes.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
