/* global Ext, go */

go.customfields.type.TemplateDialog = Ext.extend(go.customfields.FieldDialog, {
    height: dp(500),
    initFormItems : function() {
        var items =  go.customfields.type.TemplateDialog.superclass.initFormItems.call(this);

        items[0].items  = items[0].items.concat([{
            xtype: "box",
            html: t("You can insert entity properties by using curly braces e.g {{id}}.")
        },{
            xtype: "textarea",
            name: "options.template",
            fieldLabel: t("Template"),
            grow: true,
            anchor: "100%"
        }]);

        return items;
    }
});
