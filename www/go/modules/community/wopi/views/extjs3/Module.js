go.Modules.register("community", "wopi", {
	// mainPanel: "go.modules.community.newsletters.MainPanel",
	title: t("Online Office"),
	entities: [{
			name: "WopiService"
	}],
	initModule: function () {},
	systemSettingsPanels: ["go.modules.community.wopi.SystemSettingsPanel"]
});