go.Modules.register("community", "privacy", {
	title: t("Privacy"),
	entities: [],
	/**
	 * These panels will show in the System settings
	 */
	systemSettingsPanels: [
		"go.modules.community.privacy.SystemSettingsPanel",
	],

	initModule: function () {}
});
