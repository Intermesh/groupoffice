go.modules.community.tasks.AlertFields = Ext.extend(go.form.FormGroup, {
    xtype: "formgroup",
    name: "alerts",
    hideLabel: true,
    iconCls: 'ic-alert',
    mapKey: 'id',

    // this will add dp(16) padding between rows.
    pad: true,
    btnCfg: {text: t('Add alert')},
    startWithItem: false,
    itemCfg: {
        anchor: "100%",
        items: [{
            xtype: "formcontainer",
            name: "trigger",
            hideLabel: true,
            items: [{
                anchor: "100%",
                fieldLabel: t("When"),
                xtype: "datetimefield",
                name: 'when',
                hideLabel: true
            }]
        }]
    }
});
