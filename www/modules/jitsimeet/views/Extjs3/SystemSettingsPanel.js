GO.jitsimeet.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

    initComponent: function () {

        Ext.apply(this, {
            title: t('Jitsi meet', 'jitsimeet', 'legacy'),
            autoScroll: true,
            iconCls: 'ic-video-call',
            items: [
                {
                    xtype: 'fieldset',
                        title: t('Jitsi meet'),
                        defaults: {
                        anchor: '100%',
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: t('Server URL', 'jitsimeet', 'legacy'),
                            name: 'jitsiUri',
                        },
                    ]
                },
                {
                    xtype: 'fieldset',
                    title: t('JWT authentification'),
                    defaults: {
                        anchor: '100%',
                    },
                    items: [
                        {
                            hideLabel: true,
                            xtype: "checkbox",
                            boxLabel: t("Enable JWT authentification"),
                            name: "jitsiJwtEnabled"
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: t('App Secret', 'jitsimeet', 'legacy'),
                            name: 'jitsiJwtSecret',
                        },
                        {
                            xtype: 'textfield',
                            fieldLabel: t('App ID', 'jitsimeet', 'legacy'),
                            name: 'jitsiJwtAppId',
                        },
                    ]
                },
            ]
        });

        GO.jitsimeet.SystemSettingsPanel.superclass.initComponent.call(this);
    },
});