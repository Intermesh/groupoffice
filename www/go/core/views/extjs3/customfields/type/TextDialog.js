go.customfields.type.TextDialog = Ext.extend(go.customfields.FieldDialog, {
	initFormItems: function () {
		var items = go.customfields.type.TextDialog.superclass.initFormItems.call(this);

		items[0].items = items[0].items.concat([ {
				xtype: "textfield",
				name: "default",
				fieldLabel: t("Default value"),
				anchor: "100%"
			}]);

		items[1].items = items[1].items.concat([{
			xtype: "numberfield",
			decimals: 0,
			name: "options.maxLength",
			value: 50,
			fieldLabel: t("Maximum length"),
			hint: t("If the maximum length is greater then 255 chars the column type will be TEXT instead of VARCHAR. Note that TEXT fields can't have a default value. See the MySQL/MaraDB documentation for more information.")
		}]);

		return items;
	}
});
