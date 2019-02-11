Ext.ns('go.modules.core.core');

go.Modules.register("core", 'groups', {
	title: t("Groups"),
	entities:['Group'],
	systemSettingsPanels: [
		"go.modules.core.core.SystemSettingsGroupGrid"
	]
});
