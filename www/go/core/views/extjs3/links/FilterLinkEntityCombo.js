go.links.FilterLinkEntityCombo = Ext.extend(go.form.FormContainer, {
    name: "link",
    layout: "anchor",
    initComponent: function () {

        var data = [], allEntities = go.Entities.getLinkConfigs(), id;

        allEntities.forEach(function (link) {
            id = link.entity;
            if (link.filter) {
                id += "-" + link.filter;
            } else {
                link.filter = null;
            }
            data.push([id, link.entity, link.title, link.filter, link.iconCls]);
        });


        var combo = new go.form.ComboBox({
            anchor: "100%",
            hideLabel: true,
            hiddenName: "entity",
            mode: "local",
            store: new Ext.data.ArrayStore({
                fields: ['id', 'entity', 'name', 'filter', 'iconCls'],
                data: data,
                idIndex: 0
            }),
            displayField: "name",
            valueField: "entity",
            triggerAction: "all",
            forceSelection: true
        });

        this.items = [combo];

        this.supr().initComponent.call(this);
    }
});