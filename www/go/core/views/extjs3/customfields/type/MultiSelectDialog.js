/* global go, Ext */

go.customfields.type.MultiSelectDialog = Ext.extend(go.customfields.FieldDialog, {
	height: dp(800),

	initFormItems: function () {
		var items = go.customfields.type.MultiSelectDialog.superclass.initFormItems.call(this);

		items.push({
			columnWidth: 1,
			xtype: "fieldset",
			title: t("Options"),
			items: [
				this.formGrp = new go.form.FormGroup({
					name: "dataType.options",
					fieldLabel: t("Options"),
					sortable: true,
					sortColumn: 'sortOrder',
					itemCfg: {
						items: [{
							xtype: "hidden",
							name: "id",
							value: null
						}, {
							xtype: "hidden",
							name: "sortOrder",
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
