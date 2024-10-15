go.links.FilterLinkEntityCombo = Ext.extend(go.form.FormContainer, {
    name: "link",
    layout: "anchor",
    initComponent: function () {

        this.items = [new go.links.EntityCombo({
            hideLabel: true
        })];

        this.supr().initComponent.call(this);
    },

	getRawValue: function() { //for go.core.FilterPanel
			return this.items.itemAt(0).getRawValue();
	}
});