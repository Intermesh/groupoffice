go.defaultpermissions.SystemSettingsPanel = Ext.extend(Ext.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	iconCls: 'ic-share',
	layout: "border",
	title: t("Default permissions"),
	itemId: "defaultpermissions", //makes it routable
	initComponent: function () {
		
		this.items = [
			{
				cls: "text",
				region: "north",
				autoHeight: true,
				html: t("Select an entity to manage the default permissions when new items are created.")
			}, 
			new go.defaultpermissions.DefaultPermissionsPanel({
				region:"center"
			})
		];
		
		go.defaultpermissions.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});

