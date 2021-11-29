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
            ]
        });

        GO.jitsimeet.SystemSettingsPanel.superclass.initComponent.call(this);
    },
});