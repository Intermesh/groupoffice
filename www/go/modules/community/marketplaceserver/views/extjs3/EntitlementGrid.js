/* global go, Ext, dp, t */

/**
 * Grants of a Product to a Customer. `customer` and `product` are resolved
 * one hop by the framework's relation mechanism (declared in Module.js), but
 * the customer's OWNING USER is a second hop (Entitlement -> Customer -> User)
 * that go.Relations does not chain reliably (it resolves every relation field
 * concurrently, so a nested "customer.user" relation would read entity.customer
 * before the first hop's promise settles). Instead we bulk-fetch the distinct
 * User ids ourselves after each store load and cache them locally — the same
 * "get(ids) -> entities[]" API go.Relations itself uses internally.
 */
go.modules.community.marketplaceserver.EntitlementGrid = Ext.extend(go.grid.GridPanel, {

	entityStore: "MarketplaceServerEntitlement",
	stateId: 'community-marketplaceserver-entitlement-grid',

	userCache: null,

	/**
	 * @cfg {Boolean} panelMode when embedded in EntitlementPanel: the customer is
	 *      chosen in the left list, so this grid hides its Customer column, starts
	 *      empty, and only loads once setCustomer() is called.
	 */
	panelMode: false,
	// The customer this grid is scoped to (panel mode). null = none selected yet.
	customerId: null,
	customerLabel: null,

	initComponent: function () {
		var me = this,
			actions = me.initRowActions();

		me.userCache = {};
		me.autoExpandColumn = me.panelMode ? 'product' : 'customer';

		me.store = new go.data.Store({
			fields: [
				'id',
				'customerId',
				'productId',
				{name: 'customer', type: 'relation'},
				{name: 'product', type: 'relation'},
				{name: 'expiresAt', type: 'date'},
				{name: 'revokedAt', type: 'date'},
				'source',
				'stripeSubscriptionId',
				'permissionLevel'
			],
			entityStore: "MarketplaceServerEntitlement",
			sortInfo: {field: 'id', direction: 'DESC'}
		});

		me.store.on('load', me.loadCustomerUsers, me);

		me.tbar = [
			'->',
			{xtype: 'tbsearch'},
			me.addBtn = new Ext.Button({
				iconCls: 'ic-add',
				tooltip: t("Add"),
				cls: "primary",
				// In panel mode Add is meaningless until a customer is picked.
				disabled: me.panelMode,
				handler: function () { me.onAdd(); },
				scope: me
			})
		];

		me.columns = [];
		if (!me.panelMode) {
			me.columns.push({
				id: 'customer',
				header: t("Customer", "marketplaceserver", "community"),
				dataIndex: 'customer',
				sortable: true,
				renderer: function (customer) {
					if (!customer) {
						return '-';
					}
					if (customer.companyName) {
						return Ext.util.Format.htmlEncode(customer.companyName);
					}
					var user = me.userCache[customer.userId];
					return Ext.util.Format.htmlEncode(user ? user.displayName : ('#' + customer.id));
				}
			});
		}
		me.columns = me.columns.concat([
			{
				id: 'product',
				header: t("Product", "marketplaceserver", "community"),
				dataIndex: 'product',
				width: dp(220),
				sortable: true,
				renderer: function (product) {
					return product ? Ext.util.Format.htmlEncode(product.title) : '-';
				}
			},
			{
				xtype: 'datecolumn',
				header: t("Expires at", "marketplaceserver", "community"),
				dataIndex: 'expiresAt',
				width: dp(140),
				sortable: true,
				renderer: function (v) {
					return v ? Ext.util.Format.date(v) : t("Never", "marketplaceserver", "community");
				}
			},
			{
				header: t("Source", "marketplaceserver", "community"),
				dataIndex: 'source',
				width: dp(120),
				sortable: true
			},
			{
				header: t("Status", "marketplaceserver", "community"),
				dataIndex: 'revokedAt',
				width: dp(100),
				sortable: true,
				renderer: function (v) {
					return v
						? '<span style="color:var(--go-color-error,#c00)">' + t("Revoked", "marketplaceserver", "community") + '</span>'
						: t("Active", "marketplaceserver", "community");
				}
			},
			actions
		]);

		me.plugins = [actions];

		me.selModel = new Ext.grid.RowSelectionModel();

		me.viewConfig = {
			emptyText: '<i class="icon ic-store"></i><p>' +
				(me.panelMode
					? t("Select a customer to see their licenses.", "marketplaceserver", "community")
					: t("No licenses for this customer yet. Use Add to grant a product.", "marketplaceserver", "community")) + '</p>'
		};

		go.modules.community.marketplaceserver.EntitlementGrid.superclass.initComponent.call(me);

		// Standalone grid: load on render (go.grid.GridPanel doesn't auto-load).
		// In panel mode we wait for a customer to be picked (setCustomer).
		if (!me.panelMode) {
			me.on('render', function () { me.store.load(); }, me);
		}

		// Double-click opens the editor (single click just selects); the kebab
		// menu (Edit/Delete) is the other way in.
		me.on('rowdblclick', function (grid, rowIndex, e) {
			var record = grid.getStore().getAt(rowIndex);
			if (record) {
				new go.modules.community.marketplaceserver.EntitlementDialog().load(record.data.id).show();
			}
		}, me);
	},

	/**
	 * Scope this grid to one customer (panel mode): filter + reload and enable
	 * the Add button so new grants target this customer.
	 *
	 * @param {Number} customerId
	 * @param {String} customerLabel display name for the Add dialog title
	 * @return {void}
	 */
	setCustomer: function (customerId, customerLabel) {
		this.customerId = customerId;
		this.customerLabel = customerLabel;
		this.addBtn.setDisabled(!customerId);
		// A customer is now chosen: an empty grid means "no licenses yet", not
		// "pick a customer".
		if (this.rendered) {
			this.getView().emptyText = '<i class="icon ic-store"></i><p>' +
				t("No licenses for this customer yet. Use Add to grant a product.", "marketplaceserver", "community") + '</p>';
		}
		this.store.setFilter('customer', {customerId: customerId});
		this.store.load();
	},

	/**
	 * Open the Add dialog, pre-seeding the customer in panel mode.
	 *
	 * @return {void}
	 */
	onAdd: function () {
		var cfg = {};
		if (this.panelMode) {
			if (!this.customerId) {
				return;
			}
			cfg.formValues = {customerId: this.customerId};
			cfg.customerLabel = this.customerLabel;
		}
		new go.modules.community.marketplaceserver.EntitlementDialog(cfg).show();
	},

	/**
	 * Bulk-fetch the distinct User entities behind the loaded rows' customers
	 * and refresh the view once they're cached, so the Customer column shows
	 * a display name instead of a raw customer id.
	 */
	loadCustomerUsers: function () {
		var me = this,
			ids = [];

		me.store.each(function (rec) {
			var customer = rec.get('customer');
			if (customer && customer.userId && !me.userCache[customer.userId] && ids.indexOf(customer.userId) === -1) {
				ids.push(customer.userId);
			}
		});

		if (!ids.length) {
			return;
		}

		go.Db.store("User").get(ids).then(function (result) {
			(result.entities || []).forEach(function (u) {
				me.userCache[u.id] = u;
			});
			if (me.rendered) {
				me.getView().refresh();
			}
		});
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
		var me = this,
			revoked = !!record.data.revokedAt;

		// Rebuilt each time so the Revoke/Restore item reflects THIS row's state.
		if (me.moreMenu) {
			me.moreMenu.destroy();
		}
		me.moreMenu = new Ext.menu.Menu({
			items: [
				{
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function () {
						new go.modules.community.marketplaceserver.EntitlementDialog().load(me.moreMenu.record.id).show();
					},
					scope: me
				},
				revoked
					? {
						iconCls: 'ic-check-circle',
						text: t("Restore", "marketplaceserver", "community"),
						handler: function () { me.setRevoked(me.moreMenu.record.id, false); },
						scope: me
					}
					: {
						iconCls: 'ic-block',
						text: t("Revoke", "marketplaceserver", "community"),
						handler: function () {
							Ext.MessageBox.confirm(
								t("Revoke", "marketplaceserver", "community"),
								t("Revoke this license? The customer's instances lose it at their next refresh (within hours). The grant is kept and can be restored.", "marketplaceserver", "community"),
								function (btn) {
									if (btn === "yes") { me.setRevoked(me.moreMenu.record.id, true); }
								},
								me
							);
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
								go.Db.store("MarketplaceServerEntitlement").set({destroy: [rec.id]});
							},
							me
						);
					},
					scope: me
				}
			]
		});
		me.moreMenu.record = record;
		me.moreMenu.showAt(e.getXY());
	},

	/**
	 * Toggle an entitlement's revoked state via JMAP. Sets revokedAt to now (a
	 * server ISO string) to revoke, or null to restore; the grid reloads on the
	 * store's own change propagation.
	 *
	 * @param {Number} id
	 * @param {Boolean} revoke
	 * @return {void}
	 */
	setRevoked: function (id, revoke) {
		var update = {};
		update[id] = {revokedAt: revoke ? (new Date()) : null};
		go.Db.store("MarketplaceServerEntitlement").set({update: update});
	}
});
