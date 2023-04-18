go.modules.community.apikeys.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {
	iconCls: 'ic-lock',
	layout: "border",
	initComponent: function () {
		this.title = t("API Keys");		
		
		this.items = [
			{
				cls: "text",
				region: "north",
				autoHeight: true,
				html: t("API keys can be used for other services to connect to the API. A website feeding contact information for example.")
			}, 
			new go.modules.community.apikeys.KeyGrid({
				region:"center"
			})
		];
		
		go.modules.community.apikeys.SystemSettingsPanel.superclass.initComponent.call(this);
	}
});
