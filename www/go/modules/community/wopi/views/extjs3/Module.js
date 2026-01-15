go.Modules.register("business", "wopi", {
	// mainPanel: "go.modules.business.newsletters.MainPanel",
	title: t("Online Office"),
	entities: [{
			name: "WopiService"
	}],
	initModule: function () {},
	systemSettingsPanels: ["go.modules.business.wopi.SystemSettingsPanel"]
});