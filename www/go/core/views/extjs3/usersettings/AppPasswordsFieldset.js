/**
 * Fieldset in the account settings panel to manage the app passwords of the user.
 */
go.usersettings.AppPasswordsFieldset = Ext.extend(Ext.form.FieldSet, {
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
		});


		this.grid = new go.grid.GridPanel({
			store: this.store,
			autoHeight: true,
			anchor: "100%",
			border: true,
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
			// Hidden until it's known whose settings these are.
			hidden: true,
			items: [
				{
					xtype: "box",
					autoEl: "p",
					html: t("Use app passwords to let apps like WebDAV, CalDAV, CardDAV and ActiveSync log in without your regular password.")
				},
				this.newButton = new Ext.Button({
					text: t('New app password'),
					iconCls: 'ic-add',
					style: "margin-bottom: " + dp(8) + "px",
					handler: function () {
						const dlg = new go.usersettings.AppPasswordDialog();

						dlg.setValues({userId: this.userId});

						dlg.show();
					},
					scope: this
				}),
				this.grid
			]
		});

		go.usersettings.AppPasswordsFieldset.superclass.initComponent.call(this);
	},
	initMoreMenu: function () {
		this.moreMenu = new Ext.menu.Menu({
			items: [
				{
					itemId: "delete",
					iconCls: "ic-delete",
					text: t("Delete"),
					handler: (item) => {
						const record = this.store.getAt(item.parentMenu.rowIndex);

						Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to delete this password? This cannot be undone."), (btn) => {
							if (btn === "yes") {
								go.Db.store("AppPassword").destroy(record.data.id);
							}
						});
					}
				}
			]
		});

		return this.moreMenu;
	},
	/**
	 * Called by the AccountSettingsPanel
	 */
	onLoadComplete: function (data) {
		this.userId = data.id;

		// Only the user itself can create an app password as the secret is shown once to the creator.
		// Admins can see and delete the app passwords of other users.
		const own = data.id == go.User.id;
		this.newButton.setVisible(own);
		this.setVisible(own || go.User.isAdmin);

		if (own || go.User.isAdmin) {
			this.store.setFilter("user", {user: data.id});
			this.store.load();
		}
	}
});