go.modules.core.customfields.type.TextDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	initFormItems: function () {
		var items = go.modules.core.customfields.type.TextDialog.superclass.initFormItems.call(this);

		items[0].items = items[0].items.concat([{
				xtype: "numberfield",
				decimals: 0,
				name: "options.maxLength",
				value: 50,
				fieldLabel: t("Maximum length")
			}, {
				xtype: "textfield",
				name: "default",
				fieldLabel: t("Default value"),
				anchor: "100%"
			}]);

		return items;
	}
});
