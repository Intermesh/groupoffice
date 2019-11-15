go.modules.community.dokuwiki.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

    initComponent: function () {

        Ext.apply(this, {
            title: t('Dokuwiki', 'dokuwiki'),
            autoScroll: true,
            iconCls: 'ic-book',
            items: [
                {
                    xtype: "fieldset",
                    defaults: {
                        width: dp(240)
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            name: 'externalUrl',
                            fieldLabel: t('URL'),
                            hint: t('Used as dokuwiki URL')
                        },
                        {
                            xtype: 'textfield',
                            name: 'title',
                            fieldLabel: t('Title'),
                            hint: t('Used as dokuwiki page title')
                        }
                    ]
                }
            ]
        });

        go.modules.community.dokuwiki.SystemSettingsPanel.superclass.initComponent.call(this);
    }
});