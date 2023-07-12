/* global go, Ext */

go.customfields.type.SelectDialog = Ext.extend(go.customfields.FieldDialog, {
	deferredRender: false,
	initFormItems: function () {
		const items = go.customfields.type.SelectDialog.superclass.initFormItems.call(this);

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
