GO.email.TemplateCombo = Ext.extend(go.form.ComboBoxReset, {

    initComponent: function () {

        Ext.apply(this, {
            valueField: 'id',
            displayField: 'name',
            triggerAction: 'all',
            selectOnFocus: true,
            store: new GO.data.JsonStore({
                url: GO.url('email/template/store'),
                baseParams: {
                    permissionLevel: GO.permissionLevels.write
                },
                root: 'results',
                id: 'id',
                fields: ['id', 'user_id', 'user_name', 'name', 'type', 'acl_id', 'extension', 'htmlSpecialChars'],
                remoteSort: true,
                setFilter: Ext.emptyFn,
                autoLoad: true,
            }),
            //custom template
            tpl: new Ext.XTemplate(
                '<tpl for=".">',
                // name is HTML encoded by the server. The tooltip is HTML, so it's encoded again for the attribute.
                '<div ext:qtip="{name:htmlEncode} ({[Ext.util.Format.htmlEncode(Ext.util.Format.htmlEncode(values.user_name))]})" class="x-combo-list-item">{name} ({user_name:htmlEncode})</div>',
                '</tpl>',
            ),
        });

        this.store.setDefaultSort('name', 'asc');

        GO.email.TemplateCombo.superclass.initComponent.call(this);

        //hack because we dont use entity store
        this.store.on("load", function() {
            this.setValue(this.getValue());
        }, this);
    },
});