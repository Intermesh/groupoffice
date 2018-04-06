go.modules.comments.CategoryForm = Ext.extend(go.form.FormWindow, {
	stateId: 'comments-categoryForm',
	title: t('Category', 'comments'),
	entityStore: go.Stores.get("CommentCategory"),
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',
						required: true
					}]
			}
		];
	}
});