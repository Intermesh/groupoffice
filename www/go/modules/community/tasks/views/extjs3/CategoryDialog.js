go.modules.community.tasks.CategoryDialog = Ext.extend(go.form.Dialog, {
	title: t("Categories", "tasks"),
	entityStore: "TaskCategory",
	titleField: "name",
	width: dp(800),
	height: dp(600),
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						allowBlank: false
					}]
			}
		];
	}
});
