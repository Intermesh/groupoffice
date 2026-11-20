go.customfields.EntityDialog = Ext.extend(go.Window, {
	entity: null,
	modal: true,
	maximized: true,
	layout: 'fit',
	initComponent: function() {
		
		this.items = [new go.customfields.EntityPanel({
				entity: this.entity
		})];
	
		this.title = t("Custom fields") + ": " + go.Entities.get(this.entity).title;
		
		go.customfields.EntityDialog.superclass.initComponent.call(this);
	}
});
