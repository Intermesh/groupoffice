/* global go, Ext */

go.customfields.type.SelectDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	formPanelLayout:"border",
	// initComponent: function() {
	// 	this.supr().initComponent.call(this);
	// },
	initFormItems: function () {
		var items = go.customfields.type.SelectDialog.superclass.initFormItems.call(this);
		items[0].autoHeight = true;
		items[0].region = "north";

		items.push(
			new go.customfields.type.SelectOptionsTree({
				region:"center",
				name: "dataType.options",
				hideLabel: true
			})
		);

		return items;
	}
});
