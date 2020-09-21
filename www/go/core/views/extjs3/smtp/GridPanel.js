
go.smtp.GridPanel = Ext.extend(go.grid.GridPanel, {
	module: null,
	viewConfig: {
		forceFit: true,
		autoFill: true,
		sortable: false,
		emptyText: 	'<p>' +t("No items to display") + '</p>'
	},

	initComponent: function () {

		var actions = this.initRowActions();

		Ext.apply(this, {
			tbar: [
				{
					xtype:'tbtitle',
					text: t("SMTP Accounts")
				},
				'->',
				{
					iconCls: 'ic-add',
					handler: function() {
						var dlg = new go.smtp.AccountDialog();
						dlg.setValues({module: this.module}).show();
					},
					scope: this
			}],
			
			store: new go.data.Store({
				fields: ['id', 'hostname', 'fromEmail'],
				entityStore: "SmtpAccount",
				filters: {
					module: {module: this.module}
				}	
			}),
			autoHeight: true,
			plugins: [actions],
			columns: [
				{
					id: 'hostname',
					header: t('Hostname'),
					sortable: this.viewConfig.sortable,
					dataIndex: 'hostname',
					hideable: false,
					draggable: false,
					menuDisabled: true,
					width: 'auto'
				},{
					id: 'fromEmail',
					header: t('E-mail'),
					sortable: this.viewConfig.sortable,
					dataIndex: 'fromEmail',
					hideable: false,
					draggable: false,
					menuDisabled: true,
					width: 'auto'
				},
				actions
			],
			listeners : {
				render: function() {
					this.store.load();
				},
				scope: this
			}
		});

		go.smtp.GridPanel.superclass.initComponent.call(this);

		this.on("rowdblclick", function(grid, rowIndex, e) {
			var record = grid.getStore().getAt(rowIndex);
			this.edit(record.data.id);
		}, this);
	},
	
	
	//This reloads the domains combo after changes. 
	entityStore: "SmtpAccount",	

	initRowActions: function () {

		var actions = new Ext.ux.grid.RowActions({
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
	
	showMoreMenu : function(record, e) {
		if(!this.moreMenu) {
			this.moreMenu = new Ext.menu.Menu({
				items: [
					{
						itemId: "edit",
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function() {
							
							this.edit(this.moreMenu.record.data.id);
							
						},
						scope: this
					},{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function() {
							this.getSelectionModel().selectRecords([this.moreMenu.record]);
							this.deleteSelected();
						},
						scope: this
					}
					
				]
			});
		}	
		
		this.moreMenu.record = record;		
		this.moreMenu.showAt(e.getXY());
	},

	edit: function(id) {
		var dlg = new go.smtp.AccountDialog();
		dlg.load(id).show();
	}
});