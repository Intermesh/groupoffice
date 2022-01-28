
go.modules.community.oauth2client.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	hasPermission: function() {
		return go.User.isAdmin;
	},
	title: t("OAuth2 Client Settings","oauth2_client", "community"),
	iconCls: 'ic-refresh',
	labelWidth: 125,
	itemId: "oauth2client", //makes it routable
	layout: "border",

	initComponent: function () {
		this.items = [
				new go.modules.community.oauth2client.ClientGrid({
					region:"center",
					width: dp(800)
				}),
			];

		go.modules.community.oauth2client.SystemSettingsPanel.superclass.initComponent.call(this);

	}
});
