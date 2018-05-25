GO.email.LabelDialog = Ext.extend(GO.dialog.TabbedFormDialog, {

    initComponent: function () {

        Ext.apply(this, {
            titleField: 'name',
            goDialogId: 'label',
            title: t("Label", "email"),
            height: 260,
            width: 490,
            formControllerUrl: 'email/label'
        });

        GO.email.LabelDialog.superclass.initComponent.call(this);
    },

    buildForm: function () {

        this.propertiesPanel = new Ext.Panel({
            title: t("Properties"),
            cls: 'go-form-panel',
            layout: 'form',
            labelWidth: 160,
            items: [
                {
                    xtype: 'textfield',
                    name: 'name',
                    width: 300,
                    anchor: '100%',
                    maxLength: 100,
                    allowBlank: false,
                    fieldLabel: t("Name")
                },
                this.colorField = new GO.form.ColorField({
                    fieldLabel: t("Color"),
                    width: 100,
                    value: "7A7AFF",
                    name: 'color'
                })
            ]
        });

        this.addPanel(this.propertiesPanel);
    }
})
;
