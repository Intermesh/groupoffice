/* global go, Ext */

go.customfields.type.MultiSelectDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.customfields.type.SelectDialog.superclass.initFormItems.call(this);



		items.push({
			xtype: "fieldset",
			title: t("Options"),
			items: [				
				new go.form.FormGroup({
					name: "dataType.options",
					fieldLabel: t("Options"),
					itemCfg: {					
						items: [{
							xtype: "hidden",
							name: "id",
							value: null
						},{
							hideLabel: true,
							xtype: "textfield",
							name: "text",
							anchor: "100%"
						}]
					}
				})
			]
		});

		return items;
	}
});
