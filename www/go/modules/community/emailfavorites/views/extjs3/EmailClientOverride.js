GO.moduleManager.onModuleReady('email', function () {
	Ext.override(GO.email.EmailClient, {
		initComponent: GO.email.EmailClient.prototype.initComponent.createSequence(function () {
			const store = new go.data.Store({
				fields: ['id', 'userId', 'name', 'mailbox', 'account_id'],
				entityStore: "Favoritefolder",
				headers: false,
				filters: {
					user: {userId: go.User.id}
				}
			});

			const actions = new Ext.ux.grid.RowActions({
				menuDisabled: true,
				hideable: false,
				draggable: false,
				hidden: false,
				fixed: true,
				autoWidth: true,
				width: dp(24),
				actions: [{
					iconCls: "ic-more-vert"
				}]
			});

			const moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "rename",
						iconCls: "ic-edit",
						text: t("Rename"),
						handler: function (item) {
							const record = item.parentMenu.record;
							const dlg = new go.emailfavorites.FavoritefolderDialog();

							dlg.load(record.data.id);
							dlg.show();
						}
					},
					{
						itemId: "delete",
						iconCls: "ic-delete",
						text: t("Delete"),
						handler: function (item) {
							const record = item.parentMenu.record;

							Ext.MessageBox.confirm(t("Delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn === "yes") {
									go.Db.store("Favoritefolder").destroy(record.data.id).catch((result) => {

										GO.errorDialog.show(result.description);
									});
								}
							}, this);
						}
					}
				]
			});

			actions.on({
				action: function (grid, record, action, row, col, e) {
					moreMenu.record = record;
					moreMenu.showAt(e.getXY());
				},
				scope: this
			});

			this.favoritesGrid = new go.grid.GridPanel({
				cls: "go-border-bottom",
				style: "position: sticky;" +
					"top: 0;" +
					"z-index: 2;" +
					"background-color: var(--bg-mid);",
				autoHeight: true,
				hidden: true,
				viewConfig: {
					scrollOffset: 0,
					emptyText: '',
					forceFit: true
				},
				autoExpandColumn: 'name',
				hideHeaders: true,
				selectFirst: false,
				plugins: [actions],
				store: store,
				columns: [
					{
						id: 'icon',
						header: '',
						align: 'left',
						width: dp(24),
						renderer: function (v, meta) {
							return `<span style="color: var(--c-primary)" class="icon ic-star"></span>`
						}
					},
					{
						id: "name",
						dataIndex: "name",
						header: t("Name"),
						align: 'left'
					},
					actions
				]
			});

			this.favoritesGrid.on('rowclick', function (grid, rowIndex) {
				const record = grid.store.getAt(rowIndex);
				if (!record) {
					return;
				}

				const accountId = record.data.account_id;
				const mailbox = record.data.mailbox;

				const nodeId = btoa('f_' + accountId + '_' + mailbox);

				const node = this.treePanel.getNodeById(nodeId);

				if (node) {
					let parent = node.parentNode;
					while (parent && !parent.isRoot) {
						if (!parent.expanded) {
							parent.expand();
						}
						parent = parent.parentNode;
					}

					this.treePanel.getSelectionModel().select(node);

					node.ensureVisible();
				}
			}, this);

			this.favoritesGrid.on('render', function (fg) {
				const dropTarget = new Ext.dd.DropTarget(this.favoritesGrid.getView().mainBody, {
					ddGroup: 'EmailDD',
					notifyDrop: function (dd, e, data) {
						if (data.selections) {
							const target = e.getTarget(this.favoritesGrid.getView().rowSelector);
							if (target) {
								const rowIndex = this.favoritesGrid.getView().findRowIndex(target);
								const targetRecord = this.favoritesGrid.store.getAt(rowIndex);

								if (targetRecord) {
									const nodeId = btoa('f_' + targetRecord.data.account_id + '_' + targetRecord.data.mailbox);
									const node = this.treePanel.getNodeById(nodeId);

									if (node) {
										const fakeEvent = {
											target: node,
											data: data,
											dropNode: null,
											rawEvent: e.browserEvent
										};

										this.treePanel.fireEvent('beforenodedrop', fakeEvent);
									}
								}
							}
						}

						return true;
					}.bind(this),

					notifyOver: function (dd, e, data) {
						const target = e.getTarget(this.favoritesGrid.getView().rowSelector);
						if (target) {
							return dd.dropAllowed;
						}
						return dd.dropNotAllowed;
					}.bind(this)
				});
			}, this);

			this.favoritesGrid.store.on('load', function (fg) {
				if (fg.data.length > 0) {
					this.favoritesGrid.show();
				}
			}, this);

			this.favoritesGrid.store.on('changes', function (fg) {
				if (fg.data.length === 0) {
					this.favoritesGrid.hide();
				}
			}, this);

			this.treePanel.on('resize', function (panel, adjWidth) {
				if (this.favoritesGrid.rendered) {
					this.favoritesGrid.setWidth(adjWidth);
					this.favoritesGrid.doLayout();
				}
			}, this);

			this.treePanel.insert(0, this.favoritesGrid);

			store.load();
		})
	});
});