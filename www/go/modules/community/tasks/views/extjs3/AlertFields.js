go.modules.community.tasks.AlertFields = Ext.extend(go.form.FormGroup, {
    xtype: "formgroup",
    name: "alerts",
    hideLabel: true,

    // this will add dp(16) padding between rows.
    pad: true,
    btnCfg: {text: t('Add alert')},

    //the itemCfg is used to create a component for each "album" in the array.
    itemCfg: {
        layout: "form",
        defaults: {
            anchor: "99%"
        },
        items: [{
            xtype: 'datefield',
            name: 'remindDate',
            fieldLabel: t("Date"),
        }, {
            xtype: 'nativetimefield',
            name: 'remindTime',
            fieldLabel: t("Time"),
        }]
    }
});
