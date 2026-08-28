go.usersettings.AppPasswordsPanel = Ext.extend(Ext.Panel, {
	initComponent: function () {
		this.store = new go.data.Store({
			fields: [
				'id',
				'label',
				'scopes',
				{name: 'createdAt', type: 'date'},
				{name: 'lastUsedAt', type: 'date'},
				'lastUsedIp'
			],
			entityStore: "AppPassword",
			sortInfo: {
				field: "createdAt",
				direction: "DESC"
			},
			filters: {
				user: {user: go.User.id},
				isRevoked: {isRevoked: false}
			},
		});


		this.grid = new go.grid.GridPanel({
			store: this.store,
			border: false,
			autoExpandColumn: 'label',
			columns: [
				{
					id: 'label',
					header: t('Label'),
					dataIndex: 'label',
					sortable: true
				},
				{
					header: t('Protocols'),
					dataIndex: 'scopes',
					sortable: false,
					width: dp(160),
					renderer: function (value) {
						return value.map(v => v.protocol).join(', ');
					}
				},
				{
					xtype: 'datecolumn',
					header: t('Created'),
					dataIndex: 'createdAt',
					sortable: true,
					width: dp(140)
				},
				{
					xtype: 'datecolumn',
					header: t('Last used'),
					dataIndex: 'lastUsedAt',
					sortable: true,
					width: dp(140)
				},
				{
					header: t('Last used IP'),
					dataIndex: 'lastUsedIp',
					sortable: true,
					width: dp(140)
				}
			],
			viewConfig: {
				emptyText: '<i>description</i><p>' + t("No items to display") + '</p>',
				actionConfig: {
					scope: this,
					menu: this.initMoreMenu()
				}
			}
		});

		Ext.apply(this, {
			title: t('App passwords'),
			iconCls: 'ic-password',
			layout: 'fit',
			tbar: [
				{
					text: t('New app password'),
					iconCls: 'ic-add',
					handler: function () {
						let dlg = new go.usersettings.AppPasswordDialog();

						dlg.setValues({userId: this.userId});

						dlg.show();
					},
					scope: this
				}
			],
			items: [this.grid]
		});

		this.store.load();

		go.usersettings.AppPasswordsPanel.superclass.initComponent.call(this);
	},
	initMoreMenu: function () {
		this.moreMenu = new Ext.menu.Menu({
			items: [
				{
					itemId: "revoke",
					iconCls: "ic-delete",
					text: t("Revoke"),
					handler: (item) => {
						const record = this.store.getAt(item.parentMenu.rowIndex);

						Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to revoke this password? This cannot be undone."), (btn) => {
							if (btn === "yes") {
								let now = new Date().format('Y-m-d H:i:s')

								go.Db.store("AppPassword").save({revokedAt: now}, record.data.id);
							}
						});
					}
				}
			]
		});

		return this.moreMenu;
	},
	onLoadStart: function (userId) {
		this.userId = userId;
	}
});