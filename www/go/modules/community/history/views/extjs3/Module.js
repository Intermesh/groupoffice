go.Modules.register("community", "history", {
	mainPanel: "go.modules.community.history.MainPanel",
	title: t("History"),
	entities: [{
		name:'LogEntry',
		relations: {
			creator: {store: 'User', fk:'createdBy'}
		},
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: "Query"
			},
			{
				title: t("Entity ID"),
				name: 'entityId',
				multiple: true,
				type: 'number'
			}]
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
