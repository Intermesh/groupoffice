go.Modules.register("community", "oauth2client", {
	// mainPanel: "go.modules.community.oauth2client.MainPanel",
	title: t("OAuth2 client"),
	entities: ['DefaultClient'],
	initModule: function () {},
	/**
	 * These panels will show in the System settings
	 */
	systemSettingsPanels: [
		"go.modules.community.oauth2client.SystemSettingsPanel",
	],

});
