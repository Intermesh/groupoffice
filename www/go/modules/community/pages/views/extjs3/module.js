go.Modules.register("community", "pages", {
	mainPanel: "go.modules.community.pages.MainPanel",
	title: t("Pages"),
	entities: ["Page", "Site"],
	systemSettingsPanels: [
	    "go.modules.community.pages.SystemSettingsSitesGrid"
	],
	initModule: function () {}
});
