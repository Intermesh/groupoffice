
go.customfields.filter.Select = Ext.extend(go.customfields.type.TreeSelectField, {
	name: "value",
	initComponent : function() {
		this.customfield = this.filter.customfield;
		go.customfields.filter.Select.superclass.initComponent.call(this);
	}
});