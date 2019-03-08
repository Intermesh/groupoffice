go.systemsettings.defaultpermissions.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	iconCls: 'ic-share',
	layout: "border",
	title: t("Default permissions"),
	initComponent: function () {
		
		this.items = [
			{
				cls: "text",
				region: "north",
				autoHeight: true,
				html: t("Select an entity to manage the default permissions when new items are created.")
			}, 
			new go.systemsettings.defaultpermissions.DefaultPermissionsPanel({
				region:"center"
			})
		];
		
		go.systemsettings.defaultpermissions.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});

