GO.email.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-email',
	autoScroll: true,
	initComponent: function () {
		this.title = t("E-mail");		
		
		this.items = [new GO.email.TemplateGrid()];
		
		
		GO.email.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});

