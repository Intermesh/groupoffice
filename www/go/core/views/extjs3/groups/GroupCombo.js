go.modules.core.core.GroupCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Group"),
	hiddenName: 'groupId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'name'],
				entityStore: go.Stores.get("Group")
			})
		});
		
		go.modules.core.core.GroupCombo.superclass.initComponent.call(this);

	}
});


Ext.reg("groupcombo", go.modules.core.core.GroupCombo);
