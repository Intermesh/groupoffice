/* global go, Ext, dp, t */

/**
 * Two-column Entitlements manager: a searchable customer list on the left, and
 * the selected customer's licenses (entitlements) in the centre. Picking a
 * customer on the left scopes the centre grid to that customer and lets Add
 * create a grant for them — so there's no customer picker in the dialog at all.
 */
go.modules.community.marketplaceserver.EntitlementPanel = Ext.extend(Ext.Panel, {
	layout: 'border',
	border: false,

	initComponent: function () {
		var me = this;

		me.customerGrid = me.buildCustomerGrid();
		me.entitlementGrid = new go.modules.community.marketplaceserver.EntitlementGrid({
			region: 'center',
			border: false,
			panelMode: true
		});

		me.items = [me.customerGrid, me.entitlementGrid];

		go.modules.community.marketplaceserver.EntitlementPanel.superclass.initComponent.call(me);

		me.customerGrid.on('render', function () { me.customerGrid.store.load(); }, me);

		// go.grid.GridPanel fires 'navigate' on single click.
		me.customerGrid.on('navigate', function (grid, rowIndex) {
			var rec = grid.getStore().getAt(rowIndex);
			if (rec) {
				me.entitlementGrid.setCustomer(rec.id, me.customerLabel(rec));
			}
		}, me);
	},

	/**
	 * Display name for a customer row: companyName, else the owning user's name.
	 *
	 * @param {Ext.data.Record} rec
	 * @return {String}
	 */
	customerLabel: function (rec) {
		if (rec.get('companyName')) {
			return rec.get('companyName');
		}
		var user = rec.get('user');
		return (user && (user.displayName || user.email)) || ('#' + rec.id);
	},

	/**
	 * The left customer list: searchable, single column (company / user name).
	 *
	 * @return {go.grid.GridPanel}
	 */
	buildCustomerGrid: function () {
		var me = this;

		return new go.grid.GridPanel({
			region: 'west',
			width: dp(300),
			split: true,
			border: false,
			autoExpandColumn: 'customer',
			store: new go.data.Store({
				fields: [
					'id',
					'userId',
					'companyName',
					{name: 'user', type: 'relation'}
				],
				entityStore: "MarketplaceServerCustomer",
				sortInfo: {field: 'companyName', direction: 'ASC'}
			}),
			tbar: [
				{xtype: 'tbsearch'}
			],
			viewConfig: {
				emptyText: '<i class="icon ic-store"></i><p>' + t("No records to display") + '</p>'
			},
			columns: [
				{
					id: 'customer',
					header: t("Customer", "marketplaceserver", "community"),
					dataIndex: 'companyName',
					sortable: false,
					renderer: function (v, meta, rec) {
						return Ext.util.Format.htmlEncode(me.customerLabel(rec));
					}
				}
			]
		});
	}
});
