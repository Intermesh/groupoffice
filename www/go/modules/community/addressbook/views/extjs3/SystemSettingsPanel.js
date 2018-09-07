go.modules.community.addressbook.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-contacts',
	autoScroll: true,
	initComponent: function () {
		this.title = t("Address book");		
		
		this.items = [new go.modules.community.addressbook.CustomFieldCategoryPanel()];
		
		
		go.modules.community.addressbook.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
