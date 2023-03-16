go.customfields.CustomFieldRelationGrid = Ext.extend(go.grid.GridPanel, {
	layout: "fit",
	collapsible: true,

	entityId:null,
	entity:null,
	fieldId: null,
	currentId: null,

	autoHeight: true,
	maxHeight: dp(300),//gridtrait implements this.

	title: "",
	hidden: true,

	initComponent: function() {
		go.customfields.CustomFieldRelationGrid.superclass.initComponent.call(this);
	},


});
