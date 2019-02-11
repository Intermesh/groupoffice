Ext.ns('go.modules.core.core');

go.Modules.register("core", 'core', {
	title: t("Core"),
	entities: ['Acl'],
	systemSettingsPanels: [
		"go.modules.core.core.SystemSettingsCronGrid",
		"go.modules.core.core.SystemSettingsTools"
	]
});