/* global go, Ext */

go.modules.core.customfields.type.MultiSelectDialog = Ext.extend(go.modules.core.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.customfields.type.SelectDialog.superclass.initFormItems.call(this);



		items.push({
			xtype: "fieldset",
			title: t("Options"),
			items: [				
				new go.form.FormGroup({
					name: "dataType.options",
					fieldLabel: t("Options"),
					itemCfg: {
						layout: "form",
						items: [{
								xtype: "hidden",
								name: "id"
							}, {
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
