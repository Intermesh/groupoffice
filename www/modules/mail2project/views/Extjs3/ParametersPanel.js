Ext.override(go.cron.ParametersPanel, {
    buildForm: function (params) {

        this.setDisabled(params.length === 0);
        this.removeComponents();

        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                switch (key) {
                    case 'emailAccount':
                        this.emailAccountStore = new GO.data.JsonStore({
                            fields: ['id', 'name', 'email'],
                            url: GO.url('email/account/store'),
                            autoLoad: true,
                            listeners: {
                                load: function() {
                                    if (this.emailAccountField.value) {
                                        this.emailAccountField.setValue(this.emailAccountField.value);
                                    }
                                },
                                scope: this
                            }
                        });

                        this.emailAccountField = new GO.form.ComboBox({
                            hiddenName: 'emailAccount',
                            fieldLabel: t("E-mail account"),
                            store: this.emailAccountStore,
                            valueField: 'id',
                            displayField: 'email',
                            value: params[key],
                            mode: 'remote',
                            anchor: '100%',
                            allowBlank: false,
                            triggerAction: 'all',
                            reloadOnExpand: true
                        });

                        this.paramElements.push(this.emailAccountField);

                        this.add(this.emailAccountField);
                        break;
                    default:
                        this.addField(key, params[key], key);
                }
            }
        }
        this.doLayout(false, true);
        Ext.defer(function() {
            this.doLayout(false, true);
        }, 100, this);
    }
});
