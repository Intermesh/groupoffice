go.Modules.register("community", "oauth2client", {
	title: t("OAuth2 client"),
	entities: ['DefaultClient','Oauth2Client'],

	/**
	 * These panels will show in the System settings
	 */
	systemSettingsPanels: [
		"go.modules.community.oauth2client.SystemSettingsPanel",
	]
});
