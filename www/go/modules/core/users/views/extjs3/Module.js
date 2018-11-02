Ext.ns('go.modules.core.users');

go.Modules.register("core", 'users', {
	title: t("Users"),
	entities:['User'],
	systemSettingsPanels: [
		"go.modules.core.users.SystemSettingsUserGrid"
	],
	userSettingsPanels: [
		"go.modules.core.users.UserGroupGrid",
		"go.modules.core.users.UserSettingsWorkingWeek"
	]
});
