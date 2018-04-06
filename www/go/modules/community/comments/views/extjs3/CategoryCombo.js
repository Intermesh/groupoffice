go.modules.comments.CategoryCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Category", "comments"),
	hiddenName: 'categoryId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	value:null,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: true,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: go.stores.CommentCategory
			})
		});
		
		go.modules.comments.CategoryCombo.superclass.initComponent.call(this);

	}
});
