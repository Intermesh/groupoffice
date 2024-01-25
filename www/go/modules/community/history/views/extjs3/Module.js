go.Modules.register("community", "history", {
	mainPanel: "go.modules.community.history.MainPanel",
	title: t("History"),
	entities: [{
		name:'LogEntry',
		relations: {
			creator: {store: 'UserDisplay', fk:'createdBy'}
		},
		filters: [
			{
				wildcards: false,
				name: 'text',
				type: "string",
				multiple: false,
				title: t("Query")
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
