go.filter.FilterPanel = Ext.extend(Ext.Panel,{
	minHeight: dp(200),
	autoScroll: true,
	layout: "anchor",
	defaultAnchor: '100%',
	autoHeight:true,
	// set these 2 when creating
	store: null,
	entity: '',

	initComponent() {

		this.tbar = [
			{xtype: 'tbtitle', text: t("Filters")},
			'->',
			{xtype: 'filteraddbutton', entity: this.entity}
		];

		this.items = [
			{
				xtype: 'filtergrid',
				filterStore: this.store,
				entity: this.entity
			}, {
				xtype: 'variablefilterpanel',
				filterStore: this.store,
				entity: this.entity
			}
		];

		this.supr().initComponent.call(this);
	}
});
Ext.reg('filterpanel', go.filter.FilterPanel);