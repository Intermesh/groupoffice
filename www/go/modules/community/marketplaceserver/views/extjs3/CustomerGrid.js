/* global go, Ext, dp, t, GO */

/**
 * Customer list. Rows are normally provisioned by self-registration or lazily by
 * the backend (Customer::findOrCreateForUser), but a manager can also create one
 * manually via the Add button (needed when self-registration is disabled).
 * Double-click opens the edit dialog; the kebab (⋮) holds account-state actions.
 */
go.modules.community.marketplaceserver.CustomerGrid = Ext.extend(go.grid.GridPanel, {

	entityStore: "MarketplaceServerCustomer",
	stateId: 'community-marketplaceserver-customer-grid',
	autoExpandColumn: 'user',

	initComponent: function () {
		var me = this,
			actions = me.initRowActions();

		me.store = new go.data.Store({
			fields: [
				'id',
				'userId',
				{name: 'user', type: 'relation'},
				'companyName',
				{name: 'verifiedAt', type: 'date'},
				'stripeCustomerId',
				{name: 'createdAt', type: 'date'},
				'permissionLevel'
			],
			entityStore: "MarketplaceServerCustomer",
			sortInfo: {field: 'createdAt', direction: 'DESC'}
		});

		me.tbar = [
			'->',
			{xtype: 'tbsearch'},
			{
				iconCls: 'ic-add',
				tooltip: t("Add customer", "marketplaceserver", "community"),
				cls: "primary",
				handler: function () { me.openDialog(); }
			}
		];

		me.columns = [
			{
				id: 'user',
				header: t("User"),
				dataIndex: 'user',
				sortable: true,
				renderer: function (v) {
					return v ? Ext.util.Format.htmlEncode(v.displayName) : '-';
				}
			},
			{
				header: t("Company", "marketplaceserver", "community"),
				dataIndex: 'companyName',
				width: dp(180),
				sortable: true,
				renderer: function (v) { return v ? Ext.util.Format.htmlEncode(v) : '-'; }
			},
			{
				header: t("Status", "marketplaceserver", "community"),
				dataIndex: 'verifiedAt',
				width: dp(150),
				sortable: false,
				renderer: function (v, meta, record) {
					var user = record.get('user');
					if (user && user.enabled) {
						return '<span style="color:var(--hue-green,green)">' + t("Active", "marketplaceserver", "community") + '</span>';
					}
					if (!record.get('verifiedAt')) {
						return '<span style="color:var(--hue-orange,orange)">' + t("Pending verification", "marketplaceserver", "community") + '</span>';
					}
					return '<span style="color:var(--hue-red,red)">' + t("Disabled", "marketplaceserver", "community") + '</span>';
				}
			},
			{
				header: t("Stripe customer ID", "marketplaceserver", "community"),
				dataIndex: 'stripeCustomerId',
				width: dp(220)
			},
			{
				xtype: 'datecolumn',
				header: t("Created at"),
				dataIndex: 'createdAt',
				width: dp(140),
				sortable: true
			},
			actions
		];

		me.plugins = [actions];

		me.selModel = new Ext.grid.RowSelectionModel();

		me.viewConfig = {
			emptyText: '<i class="icon ic-store"></i><p>' +
				t("No customers yet. Customers appear here after they register from the marketplace client.", "marketplaceserver", "community") + '</p>'
		};

		go.modules.community.marketplaceserver.CustomerGrid.superclass.initComponent.call(me);

		// Standalone grid: load on render (go.grid.GridPanel doesn't auto-load).
		me.on('render', function () { me.store.load(); }, me);

		// Double-click opens the edit dialog; account-state actions stay on the
		// kebab (⋮) menu.
		me.on('rowdblclick', function (grid, rowIndex) {
			var record = grid.getStore().getAt(rowIndex);
			if (record) {
				me.openDialog(record.id);
			}
		}, me);
	},

	/**
	 * Open the customer create/edit dialog. Reloads the grid on close so a status
	 * change made live in the dialog (setEnabled) is reflected in the list.
	 *
	 * @param {Number} [id] omit to create a new customer
	 * @return {void}
	 */
	openDialog: function (id) {
		var me = this,
			dlg = new go.modules.community.marketplaceserver.CustomerDialog();
		dlg.on('close', function () {
			if (me.rendered) {
				me.store.load();
			}
		});
		if (id) {
			dlg.load(id);
		}
		dlg.show();
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
			user = record.get('user'),
			enabled = !!(user && user.enabled),
			verified = !!record.get('verifiedAt'),
			items = [];

		// Account state actions (enable/verify vs disable, plus resend for
		// accounts still awaiting verification).
		if (enabled) {
			items.push({
				iconCls: 'ic-block',
				text: t("Disable account", "marketplaceserver", "community"),
				handler: function () { me.onSetEnabled(record, false); },
				scope: me
			});
		} else {
			items.push({
				iconCls: 'ic-check-circle',
				text: verified ? t("Enable account", "marketplaceserver", "community") : t("Verify & enable", "marketplaceserver", "community"),
				handler: function () { me.onSetEnabled(record, true); },
				scope: me
			});
		}
		if (!enabled && !verified) {
			items.push({
				iconCls: 'ic-email',
				text: t("Resend verification e-mail", "marketplaceserver", "community"),
				handler: function () { me.onResendVerification(record); },
				scope: me
			});
		}

		items.push('-');
		items.push({
			iconCls: 'ic-vpn-key',
			text: t("Issue token", "marketplaceserver", "community"),
			handler: function () { me.onIssueToken(record); },
			scope: me
		});
		items.push({
			iconCls: 'ic-delete',
			text: t("Delete"),
			handler: function () {
				Ext.MessageBox.confirm(
					t("Confirm delete"),
					t("Are you sure you want to delete this item?"),
					function (btn) {
						if (btn !== "yes") return;
						go.Db.store("MarketplaceServerCustomer").set({destroy: [record.id]});
					},
					me
				);
			},
			scope: me
		});

		// Rebuilt per row so items reflect the record's current state.
		if (me.moreMenu) {
			me.moreMenu.destroy();
		}
		me.moreMenu = new Ext.menu.Menu({items: items});
		me.moreMenu.showAt(e.getXY());
	},

	/**
	 * Enable/disable a customer's account (manager action), then refresh so the
	 * status column + menu reflect the change.
	 *
	 * @param {Ext.data.Record} record
	 * @param {Boolean} enabled
	 * @return {void}
	 */
	onSetEnabled: function (record, enabled) {
		var me = this;
		go.Jmap.request({
			method: "MarketplaceServerCustomer/setEnabled",
			params: {customerId: record.id, enabled: enabled},
			callback: function (options, success, response) {
				if (!success) {
					GO.errorDialog.show((response && response.message) || t("Error"));
					return;
				}
				// Reflect the SERVER's authoritative result directly on the row.
				// The 'user' relation is resolved from a client-side cache that
				// this custom method doesn't invalidate, so a plain store reload
				// can show a STALE enabled flag — which made "Enable" look like it
				// did nothing and "Verify & enable" look like a disable.
				var user = Ext.apply({}, record.get('user') || {});
				user.enabled = !!response.enabled;
				record.set('user', user);
				if (response.enabled && !record.get('verifiedAt')) {
					record.set('verifiedAt', new Date());
				}
				record.commit();
				if (me.rendered) {
					me.getView().refresh();
				}
			}
		});
	},

	/**
	 * Re-send the verification e-mail to a customer (manager action).
	 *
	 * @param {Ext.data.Record} record
	 * @return {void}
	 */
	onResendVerification: function (record) {
		go.Jmap.request({
			method: "MarketplaceServerCustomer/resendVerification",
			params: {customerId: record.id},
			callback: function (options, success, response) {
				if (!success) {
					GO.errorDialog.show((response && response.message) || t("Error"));
					return;
				}
				go.Notifier.flyout({
					title: t("Customers", "marketplaceserver", "community"),
					description: t("Verification e-mail sent.", "marketplaceserver", "community"),
					time: 5000
				});
			}
		});
	},

	/**
	 * Manager action: issue a fresh API token for this customer (offline/invoice
	 * sales or re-issue) and show the plaintext once for copying.
	 *
	 * @param {Ext.data.Record} record
	 * @return {void}
	 */
	onIssueToken: function (record) {
		go.Jmap.request({
			method: "MarketplaceServerApiToken/issue",
			params: {customerId: record.id},
			callback: function (options, success, response) {
				if (!success) {
					GO.errorDialog.show((response && response.message) || t("Error"));
					return;
				}
				new go.Window({
					title: t("Issue token", "marketplaceserver", "community"),
					width: dp(560),
					autoHeight: true,
					modal: true,
					bodyStyle: 'padding:' + dp(12) + 'px',
					html: '<p>' + Ext.util.Format.htmlEncode(t("Copy this token now. It will not be shown again.", "marketplaceserver", "community")) + '</p>' +
						'<code style="display:block;word-break:break-all;padding:8px;background:var(--surface-variant,#eee);border-radius:4px">' +
						Ext.util.Format.htmlEncode(response.token) + '</code>',
					buttons: [{text: t("Close"), handler: function () { this.ownerCt.ownerCt.close(); }}]
				}).show();
			}
		});
	}
});
