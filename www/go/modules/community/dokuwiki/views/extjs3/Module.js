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
            title = t("title", 'dokuwiki');
        }

        this.title = title;

        this.addPanel(Ext.extend(go.modules.community.dokuwiki.MainPanel, {title: title}));
    },

    systemSettingsPanels: [
        "go.modules.community.dokuwiki.SystemSettingsPanel"
    ],
});