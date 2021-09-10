go.Modules.register("community", "history", {
	mainPanel: "go.modules.community.history.MainPanel",
	title: t("History"),
	entities: [{
		name:'LogEntry',
		relations: {
			creator: {store: 'User', fk:'createdBy'}
		}
	}],
	actionTypes: [
		"create",
		"update",
		"delete",
		"login",
		"logout"
	],
	initModule: function () {},
	systemSettingsPanels: [
		"go.modules.community.history.SystemSettingsPanel"
	]
});
