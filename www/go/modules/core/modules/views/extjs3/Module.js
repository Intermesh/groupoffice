Ext.ns('go.modules.core.modules');

go.Modules.register("core", 'modules', {
	title: t("Modules"),
	entities: ["Module"],
	systemSettingsPanels: [
		"go.modules.core.modules.SystemSettingsModuleGrid"
	]
});