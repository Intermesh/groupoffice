/* global go, Ext, dp, t */

/**
 * Manager-only grid of published module releases. Uploading a release here
 * stores the ZIP as a blob (see ReleaseDialog's blobId FileField); customers
 * never see this entity, they hit the page download endpoint instead.
 */
go.modules.community.marketplaceserver.ReleaseGrid = Ext.extend(go.grid.GridPanel, {

	entityStore: "MarketplaceServerRelease",
	stateId: 'community-marketplaceserver-release-grid',
	autoExpandColumn: 'moduleName',

	initComponent: function () {
		var me = this,
			actions = me.initRowActions();

		me.store = new go.data.Store({
			fields: [
				'id',
				'productId',
				{name: 'product', type: 'relation'},
				'moduleName',
				'version',
				'goVersion',
				'changelog',
				'blobId',
				{name: 'publishedAt', type: 'date'},
				'active',
				'permissionLevel'
			],
			entityStore: "MarketplaceServerRelease",
			sortInfo: {field: 'publishedAt', direction: 'DESC'}
		});

		// Default to one row per module+branch (the current build); the toggle
		// below reveals the full version history.
		me.store.setFilter('latest', {latest: true});

		me.tbar = {
			// Collapse overflowing items into a ">>" menu on narrow screens.
			enableOverflow: true,
			items: [
			me.latestToggle = new Ext.Button({
				enableToggle: true,
				pressed: true,
				text: t("Latest version only", "marketplaceserver", "community"),
				toggleHandler: function (btn, pressed) {
					me.store.setFilter('latest', {latest: pressed});
					me.store.load();
					btn.setText(pressed
						? t("Latest version only", "marketplaceserver", "community")
						: t("Show all versions", "marketplaceserver", "community"));
				},
				scope: me
			}),
			'->',
			{xtype: 'tbsearch'},
			{
				iconCls: 'ic-add',
				tooltip: t("Add"),
				cls: "primary",
				handler: function () {
					// Seed "published at" to now (create flow) via formValues so the
					// field baselines clean (no phantom dirty state).
					new go.modules.community.marketplaceserver.ReleaseDialog({
						formValues: {publishedAt: new Date()}
					}).show();
				},
				scope: me
			}
			]
		};

		me.columns = [
			{
				id: 'moduleName',
				header: t("Module", "marketplaceserver", "community"),
				dataIndex: 'moduleName',
				sortable: true,
				renderer: function (v, meta, rec) {
					var p = rec.get('product');
					return Ext.util.Format.htmlEncode((p && p.title) || v || '');
				}
			},
			{
				header: t("Version", "marketplaceserver", "community"),
				dataIndex: 'version',
				width: dp(110),
				sortable: true
			},
			{
				header: t("Group-Office branch", "marketplaceserver", "community"),
				dataIndex: 'goVersion',
				width: dp(130),
				sortable: true
			},
			{
				xtype: 'datecolumn',
				header: t("Published at", "marketplaceserver", "community"),
				dataIndex: 'publishedAt',
				width: dp(140),
				sortable: true
			},
			{
				header: t("Active", "marketplaceserver", "community"),
				dataIndex: 'active',
				width: dp(70),
				align: 'center',
				renderer: function (v) {
					return v ? '<i class="icon ic-check"></i>' : '<i class="icon ic-close"></i>';
				}
			},
			actions
		];

		me.plugins = [actions];

		me.selModel = new Ext.grid.RowSelectionModel();

		me.viewConfig = {
			emptyText: '<i class="icon ic-store"></i><p>' +
				t("No releases yet. Upload a module package to publish a version.", "marketplaceserver", "community") + '</p>'
		};

		go.modules.community.marketplaceserver.ReleaseGrid.superclass.initComponent.call(me);

		// Standalone grid: load on render (go.grid.GridPanel doesn't auto-load).
		me.on('render', function () { me.store.load(); }, me);

		// Double-click opens the editor (single click just selects); the kebab
		// menu (Edit/Delete) is the other way in.
		me.on('rowdblclick', function (grid, rowIndex, e) {
			var record = grid.getStore().getAt(rowIndex);
			if (record) {
				new go.modules.community.marketplaceserver.ReleaseDialog().load(record.data.id).show();
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
						iconCls: 'ic-add',
						text: t("Release new version", "marketplaceserver", "community"),
						handler: function () {
							// New release pre-filled with the same module + branch —
							// just bump the version and upload the new ZIP.
							var rec = me.moreMenu.record;
							new go.modules.community.marketplaceserver.ReleaseDialog({
								formValues: {publishedAt: new Date()},
								copyFrom: {
									productId: rec.get('productId'),
									goVersion: rec.get('goVersion')
								}
							}).show();
						},
						scope: me
					},
					{
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							new go.modules.community.marketplaceserver.ReleaseDialog().load(me.moreMenu.record.id).show();
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
									go.Db.store("MarketplaceServerRelease").set({destroy: [rec.id]});
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
