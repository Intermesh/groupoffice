/* global go, Ext */

go.modules.core.core.type.MultiSelectDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),
	initFormItems: function () {
		var items = go.modules.core.core.type.SelectDialog.superclass.initFormItems.call(this);



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
