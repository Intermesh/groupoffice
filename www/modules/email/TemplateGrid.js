
GO.email.TemplateGrid = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true
	},
	
	ownedBy: null,
	
	setOwnedBy : function(userId) {
		this.ownedBy = this.store.baseParams.filter.ownedBy = userId;
	},

	initComponent: function () {

		var actions = this.initRowActions();

		Ext.apply(this, {	
			tbar: [
				{
					xtype:'tbtitle',
					text: t("Templates")
				},
				'->',
				{
					iconCls: 'ic-add',
					handler: function() {
						var dlg = new GO.email.TemplateDialog();
						dlg.setValues({ownedBy: this.ownedBy}).show();
					},
					scope: this
			}],
			
			store: new go.data.Store({
				fields: ['id', 'name', 'aclId', "permissionLevel"],
				entityStore: "EmailTemplate",
				baseParams: {
					filter: {
						ownedBy: this.ownedBy
					}
				}
			}),
			autoHeight: true,
			plugins: [actions],
			columns: [
				{					
					header: t('Name'),
					sortable: false,
					dataIndex: 'name',
					hideable: false,
					draggable: false,
					menuDisabled: true
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

		go.modules.community.imapauthenticator.ServerGrid.superclass.initComponent.call(this);
		
		this.on("rowdblclick", function(grid, rowIndex, e) {
			this.edit(grid.store.getAt(rowIndex).data.id);
		}, this);
	},

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
						itemId:"share",
						iconCls: 'ic-share',
						text: t("Share"),
						handler: function() {
							var shareWindow = new go.permissions.ShareWindow({
								title: t("Share") + ": " + this.moreMenu.record.get('name')
							});
							
							shareWindow.load(this.moreMenu.record.get('aclId')).show();
						},
						scope: this						
					},
					'-',
					{
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
		this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		this.moreMenu.getComponent("share").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		this.moreMenu.getComponent("delete").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		
		this.moreMenu.showAt(e.getXY());
	},
	
	edit: function(id) {
		var form = new GO.email.TemplateDialog();
		form.load(id).show();
	}
});

