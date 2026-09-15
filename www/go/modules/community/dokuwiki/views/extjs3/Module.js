go.Modules.register("community", 'dokuwiki', {

	title: t("title", 'dokuwiki'),

	initModule: function () {
		var module = go.Modules.get('community', 'dokuwiki'),
			panel,
			title;

		if (module.settings) {
			title = module.settings.title;
		}

		if (go.util.empty(title)) {
			title = t("title", 'dokuwiki', 'community');
		}

		this.title = title;

		go.modules.community.dokuwiki.MainPanel.prototype.title = title;
		this.addPanel(go.modules.community.dokuwiki.MainPanel);
	},

	systemSettingsPanels: [
		"go.modules.community.dokuwiki.SystemSettingsPanel"
	],
});