/* global go, Ext, dp, t */

/**
 * Product catalog grid. Manager-only tab (see MainPanel gating) — the
 * storefront itself is served through the page API, not this JMAP entity.
 */
go.modules.community.marketplaceserver.ProductGrid = Ext.extend(go.grid.GridPanel, {

	entityStore: "MarketplaceServerProduct",
	stateId: 'community-marketplaceserver-product-grid',
	autoExpandColumn: 'title',

	TYPE_LABELS: null,

	initComponent: function () {
		var me = this,
			actions = me.initRowActions();

		me.TYPE_LABELS = {
			module: t("Module", "marketplaceserver", "community"),
			collection: t("Collection", "marketplaceserver", "community")
		};

		me.store = new go.data.Store({
			fields: [
				'id',
				'type',
				'moduleName',
				'title',
				'description',
				'price',
				'currency',
				'active',
				{name: 'availableUntil', type: 'date'},
				'sortOrder',
				'permissionLevel'
			],
			entityStore: "MarketplaceServerProduct",
			sortInfo: {field: 'sortOrder', direction: 'ASC'}
		});

		// Product-type filter: All / Modules / Collections (one active at a time),
		// driving the entity `type` filter.
		var typeBtn = function (label, type, pressed) {
			return new Ext.Button({
				text: label,
				enableToggle: true,
				toggleGroup: 'mps-product-type',
				allowDepress: false,
				pressed: !!pressed,
				toggleHandler: function (btn, isPressed) {
					if (isPressed) {
						me.store.setFilter('type', {type: type});
						me.store.load();
					}
				}
			});
		};

		me.tbar = {
			// Collapse overflowing items into a ">>" menu on narrow screens.
			enableOverflow: true,
			items: [
				typeBtn(t("All"), null, true),
				typeBtn(t("Modules", "marketplaceserver", "community"), 'module'),
				typeBtn(t("Collections", "marketplaceserver", "community"), 'collection'),
				'->',
				{xtype: 'tbsearch'},
				{
					iconCls: 'ic-add',
					tooltip: t("Add"),
					cls: "primary",
					handler: function () {
						new go.modules.community.marketplaceserver.ProductDialog().show();
					},
					scope: me
				}
			]
		};

		me.columns = [
			{
				id: 'title',
				header: t("Title"),
				dataIndex: 'title',
				sortable: true
			},
			{
				header: t("Type", "marketplaceserver", "community"),
				dataIndex: 'type',
				width: dp(120),
				sortable: true,
				renderer: function (v) {
					return Ext.util.Format.htmlEncode(me.TYPE_LABELS[v] || v);
				}
			},
			{
				header: t("Module name", "marketplaceserver", "community"),
				dataIndex: 'moduleName',
				width: dp(160),
				sortable: true
			},
			{
				header: t("Price", "marketplaceserver", "community"),
				dataIndex: 'price',
				width: dp(110),
				align: 'right',
				sortable: true,
				renderer: function (v, meta, rec) {
					if (v === null || v === undefined) {
						return '';
					}
					return Ext.util.Format.number(v, '0,000.00') + ' ' + Ext.util.Format.htmlEncode(rec.get('currency') || '');
				}
			},
			{
				header: t("Active", "marketplaceserver", "community"),
				dataIndex: 'active',
				width: dp(70),
				align: 'center',
				sortable: true,
				renderer: function (v) {
					return v ? '<i class="icon ic-check"></i>' : '<i class="icon ic-close"></i>';
				}
			},
			{
				xtype: 'datecolumn',
				header: t("Available until", "marketplaceserver", "community"),
				dataIndex: 'availableUntil',
				width: dp(130),
				sortable: true,
				renderer: function (v) {
					// Flag a product whose window has already closed (retired).
					if (!v) {
						return '';
					}
					var s = Ext.util.Format.date(v, Ext.util.Format.defaultDateFormat || 'd-m-Y');
					// Past end-of-day → retired; dim it red so it stands out in the list.
					if (v.getTime() + 86400000 <= Date.now()) {
						return '<span style="color:var(--hue-red,red)">' + s + '</span>';
					}
					return s;
				}
			},
			{
				header: t("Sort order"),
				dataIndex: 'sortOrder',
				width: dp(90),
				align: 'right',
				sortable: true
			},
			actions
		];

		me.plugins = [actions];

		me.selModel = new Ext.grid.RowSelectionModel();

		me.viewConfig = {
			emptyText: '<i class="icon ic-store"></i><p>' +
				t("No products yet. Add one to build your catalog.", "marketplaceserver", "community") + '</p>'
		};

		go.modules.community.marketplaceserver.ProductGrid.superclass.initComponent.call(me);

		// go.grid.GridPanel does NOT auto-load its store (the scroll-loader only
		// loads MORE on scroll). A standalone grid must load itself on render, or
		// it stays empty AND go.data.Store.onChanges bails out early (its guard
		// requires this.loaded) so newly-created rows never appear. See KeyGrid.
		me.on('render', function () { me.store.load(); }, me);

		// Double-click opens the editor (single click just selects); the kebab
		// menu (Edit/Delete) is the other way in.
		me.on('rowdblclick', function (grid, rowIndex, e) {
			var record = grid.getStore().getAt(rowIndex);
			if (record) {
				new go.modules.community.marketplaceserver.ProductDialog().load(record.data.id).show();
			}
		}, me);
	},

	initRowActions: function () {
		var me = this,
			actions = new Ext.ux.grid.RowActions({
				menuDisabled: true,
				hideable: false,
				draggable: false,
				fixed: true,
				width: dp(40),
				header: '',
				hideMode: 'display',
				keepSelection: true,
				actions: [{iconCls: 'ic-more-vert'}]
			});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				me.showMoreMenu(record, e);
			},
			scope: me
		});

		return actions;
	},

	showMoreMenu: function (record, e) {
		var me = this;
		if (!me.moreMenu) {
			me.moreMenu = new Ext.menu.Menu({
				items: [
					{
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							new go.modules.community.marketplaceserver.ProductDialog().load(me.moreMenu.record.id).show();
						},
						scope: me
					},
					{
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							var rec = me.moreMenu.record;
							Ext.MessageBox.confirm(
								t("Confirm delete"),
								t("Are you sure you want to delete this item?"),
								function (btn) {
									if (btn !== "yes") return;
									go.Db.store("MarketplaceServerProduct").set({destroy: [rec.id]});
								},
								me
							);
						},
						scope: me
					}
				]
			});
		}
		me.moreMenu.record = record;
		me.moreMenu.showAt(e.getXY());
	}
});
