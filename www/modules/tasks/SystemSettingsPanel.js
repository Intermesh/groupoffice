GO.tasks.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-check',
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
