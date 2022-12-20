go.links.FilterLinkEntityCombo = Ext.extend(go.form.FormContainer, {
    name: "link",
    layout: "anchor",
    initComponent: function () {

        this.items = [new go.links.EntityCombo({
            hideLabel: true
        })];

        this.supr().initComponent.call(this);
    }
});