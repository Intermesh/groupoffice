GO.jitsimeet.SystemSettingsPanel = Ext.extend(go.systemsettings.Panel, {

    initComponent: function () {

        Ext.apply(this, {
            title: t('Jitse meet', 'jitsimeet', 'legacy'),
            autoScroll: true,
            iconCls: 'ic-video',
            items: [
                {
                    xtype: 'fieldset',
                        title: t('Jitsi'),
                    defaults: {
                        anchor: '100%',
                    },
                    items: [
                        {
                            xtype: 'textfield',
                            fieldLabel: t('Jitsi uri', 'jitsimeet', 'legacy'),
                            name: 'jitsiUri',
                        },
                    ]
                },
            ]
        });

        GO.jitsimeet.SystemSettingsPanel.superclass.initComponent.call(this);
    },
});