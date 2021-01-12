/* global go, Ext */

go.customfields.type.SelectDialog = Ext.extend(go.customfields.FieldDialog, {
	// height: dp(800),
	// formPanelLayout:"border",
	// initComponent: function() {
	// 	this.supr().initComponent.call(this);
	// },
	initFormItems: function () {
		var items = go.customfields.type.SelectDialog.superclass.initFormItems.call(this);
		// items[0].autoHeight = true;
		// items[0].region = "north";

		this.addPanel({
				layout: "border",
				title: t("Options"),
				items: [{
					region: "north",
					autoHeight: true,
					xtype: "box",
					cls: "go-message-panel",
					html: t("Warning: removing select options also removes the data from the records. You can disable select options by unchecking them. ")
				}, new go.customfields.type.SelectOptionsTree({
					region: "center",

					name: "dataType.options",
					hideLabel: true
				})]
			}
		);

		return items;
	}
});
