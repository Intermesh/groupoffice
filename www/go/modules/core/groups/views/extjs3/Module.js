Ext.ns('go.modules.core.groups');

go.Modules.register("core", 'groups', {
	title: t("Groups"),
	entities:['Group'],
	systemSettingsPanels: [
		"go.modules.core.groups.SystemSettingsGroupGrid"
	]
});
