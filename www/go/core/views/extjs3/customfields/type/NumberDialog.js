go.customfields.type.NumberDialog = Ext.extend(go.customfields.FieldDialog, {
	initFormItems: function () {
		var items = go.customfields.type.NumberDialog.superclass.initFormItems.call(this);


		items[0].items  = items[0].items.concat([{
				xtype: "numberfield",
				decimals: 0,
				name: "options.numberDecimals",
				value: 2,
				fieldLabel: t("Decimals"),
				listeners: {
					scope: this,
					change: function(field, v) {
						this.formPanel.getForm().findField('default').decimals = v;
					}
				}
			}, {
				xtype: "numberfield",
				name: "default",
				serverFormats: false,
				fieldLabel: t("Default value"),
				anchor: "100%",
				decimals: 2
			}]);

		return items;
	}
});
