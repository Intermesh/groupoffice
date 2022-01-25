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
                hideLabel: true,
                listeners: {
                    afterrender: function(field) {
                        // if start date is set then use that as default for new alerts
                        if(!go.util.empty(this.getValue())) {
                            return;
                        }

                        const taskDialog = field.findParentByType("window");
                        const start = taskDialog.formPanel.form.findField("start");

                        this.setDate(start.getValue());
                    }
                }
            }]
        }]
    }
});
