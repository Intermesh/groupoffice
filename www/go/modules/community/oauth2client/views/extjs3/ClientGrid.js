go.modules.community.oauth2client.ClientGrid = Ext.extend(go.grid.GridPanel, {
	initComponent: function() {
		let actions = this.initRowActions();

		this.store = new go.data.Store({
			fields: ['name','id', 'clientId', 'clientSecret', 'projectId', 'defaultClientId'],
			sort: [{property: "name"}],
			entityStore: "Oauth2Client"
		});

		Ext.apply(this, {
			plugins: [actions],
			tbar: [{
					xtype: 'tbtitle',
					text: t("OAuth2 Connections", 'oauth2client', 'community')
				},'->',{
					iconCls: "ic-add",
					handler: function() {
						let dlg = new go.modules.community.oauth2client.ClientDialog();
						dlg.show();
					},scope: this
			}],
			columns:[{
				header: t('ID'),
				width: dp(80),
				dataIndex: 'id',
				hidden:true,
				align: "right"
			},{
				header: t('Name'),
				dataIndex: 'name',
				id: 'name'
			},{
				header: t('Client ID'),
				dataIndex: 'clientId',
				id: 'clientId'
			},{
				header: t('Client Secret'),
				dataIndex: 'clientSecret',
				id: 'clientSecret'
			},{
				header: t('Project ID'),
				dataIndex: 'projectId',
				id: 'projectId'
			},{
				header: t('Provider'),
				dataIndex: 'defaultClientId',
				id: 'providerName',
				renderer: (v) => {
					return this.defaultClients[v];
				}
			},
			actions
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true
			},
			// config options for stateful behavior
			stateful: true,
			stateId: 'clientkeys-grid'

		});

		go.modules.community.oauth2client.ClientGrid.superclass.initComponent.call(this);
		this.defaultClients = [];

		this.on('render', () => {
			go.Db.store('DefaultClient').all((success, data) => {
				for(let i=0,l=data.length;i<l;i++) {
					this.defaultClients[data[i].id] = data[i].name;
				}
			}).finally(() => {
				this.store.load();
			});
		});

		this.on("rowdblclick", function(grid, rowIndex, e) {
			let record = grid.getStore().getAt(rowIndex);
			this.edit(record.data.id);
		}, this);
	},

	initRowActions: function () {
		let actions = new Ext.ux.grid.RowActions({
			menuDisabled: true,
			hideable: false,
			draggable: false,
			fixed: true,
			header: '',
			hideMode: 'display',
			keepSelection: true,

			actions: [{
				iconCls: 'ic-more-vert'
			}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				this.showMoreMenu(record, e);
			},
			scope: this
		});

		return actions;

	},

	edit: function(id) {
		let dlg = new go.modules.community.oauth2client.ClientDialog();
		dlg.load(id).show();
	},

	showMoreMenu : function(record, e) {
		if(!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId:"edit",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function() {
							this.edit(this.moreMenu.record.id);
						},
						scope: this
					},{
						itemId:"delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function() {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
							this.deleteSelected();
						},
						scope: this
					},
				]
			})
		}

		this.moreMenu.record = record;

		this.moreMenu.showAt(e.getXY());
	}

});