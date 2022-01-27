
go.modules.community.oauth2client.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

	title: t("OAuth2 Client Settings","oauth2_client", "community"),
	iconCls: 'ic-token',
	labelWidth: 125,
	initComponent: function () {
		// Maak een grid van bestaande koppelingen, tabelkop met 'toevoeg'-knop, sortering, etc
		this.items = [{
			xtype: 'container',
			layout: 'border',
			items: [
				new go.modules.community.oauth2client.ClientGrid({
					region: 'center',
					width: dp(800)
				}),
			]
		}]
	}
});
