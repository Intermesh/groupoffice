go.modules.core.core.UserCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("User"),
	hiddenName: 'userId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'displayName',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	allowBlank: false,
	initComponent: function () {
		Ext.applyIf(this, {
			store: new go.data.Store({
				fields: ['id', 'displayName'],
				entityStore: go.Stores.get("User")
			})
		});
		
		go.modules.core.core.UserCombo.superclass.initComponent.call(this);

	}
});


Ext.reg("usercombo", go.modules.core.core.UserCombo);
