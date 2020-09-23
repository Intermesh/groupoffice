go.modules.community.tasks.AlertFields = Ext.extend(go.form.FormGroup, {
    xtype: "formgroup",
    name: "alerts",
    hideLabel: true,
    iconCls: 'ic-alert',

    // this will add dp(16) padding between rows.
    pad: true,
    btnCfg: {text: t('Add alert')},
    startWithItem: false,
    itemCfg: {
        anchor: "100%",
        items: [{
            anchor: "100%",
            xtype: "compositefield",
            hideLabel: true,
            items: [
                {
                    xtype: 'datefield',
                    name: 'remindDate',
                    fieldLabel: t("Date"),
                    width: dp(140)
                }, {
                    xtype: 'nativetimefield',
                    name: 'remindTime',
                    fieldLabel: t("Time"),
                    flex:1
                }]
        }]
    }
});
