go.filter.FilterCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Filter"),
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	store: {
		xtype: "gostore",
		fields: ['id', 'name', 'aclId', "permissionLevel", "filter"],
		entityStore: "EntityFilter"
	},

	entity: null, // set using typeConfig

	initComponent: function () {
		go.filter.FilterCombo.superclass.initComponent.call(this);
		this.store.setFilter('base', {
			entity: this.entity,
			type: "fixed"
		});
		// this.on("render", function() {
		// 	this.store.load();
		// }, this);
		
	}
});