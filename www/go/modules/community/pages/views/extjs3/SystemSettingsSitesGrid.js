go.modules.community.pages.SystemSettingsSitesGrid = Ext.extend(go.grid.GridPanel, {
	iconCls: 'ic-picture-in-picture-alt',
	initComponent: function () {
		
		var actions = this.initRowActions();
		
		this.title = t("Sites");

		this.store = new go.data.Store({
			fields: [
				'id',
				'slug',
				'aclId',
				'fileFolderId',
				'siteName',
				'documentFormat',
				{name: 'createdAt', type: 'date'},
				{name: 'modifiedAt', type: 'date'},
				{name: 'creator', type: go.data.types.User, key: 'createdBy'},
                                {name: 'modifier', type: go.data.types.User, key: 'modifiedBy'},
				'permissionLevel'
			],
			baseParams: {filter: {}},
			entityStore: go.Stores.get("Site")
		});
		
		Ext.apply(this, {
			plugins: [actions],
			tbar: ['->', 
				{
					xtype: 'tbsearch'
				},{					
					iconCls: 'ic-add',
					tooltip: t('Add'),
					handler: function (e, toolEl) {
						var dlg = new go.modules.community.pages.SiteWizard();
						dlg.show();
					}
				}
				
			],
			columns: [
				{
					id: 'siteName',
					header: t('Site'),
					width: dp(200),
					sortable: true,
					dataIndex: 'siteName',
					
				},
				{
					id: 'creator',
					header: t('created by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'creator',
					hidden: true,
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: dp(120),
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{
					id: 'modifier',
					header: t('last modified by'),
					width: dp(160),
					sortable: true,
					dataIndex: 'modifier',
					hidden: false,
					renderer: function (v) {
						return v ? v.displayName : "-";
					}
				},{
					xtype:"datecolumn",
					id: 'modifiedAt',
					header: t('last modified at'),
					width: dp(120),
					sortable: true,
					dataIndex: 'modifiedAt',
					hidden: false
				},
				actions
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
				forceFit: true,
				autoFill: true,
			},
			// config options for stateful behavior
			stateful: true,
			stateId: 'sites-grid'
		});

		go.modules.community.pages.SystemSettingsSitesGrid.superclass.initComponent.call(this);
		
		this.on('render', function() {
			this.store.load();
		}, this);
		
		this.on('rowdblclick', function(grid, rowIndex, e) {
			this.editProperties(this.store.getAt(rowIndex).id);
		}); 
		//attempt at fixing created_by/modified_by turning to default "-" after editing.
		// causes "Abort request clientCallId-11" in console on loading this component?
		//this.store.entityStore.on("changes",this.reloadStore, this);
	}, 
	
	reloadStore: function () {
	    this.store.reload();
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
						itemId: "editProp",
						iconCls: 'ic-edit',
						text: t("Edit Properties"),
						handler: function() {this.editProperties(this.moreMenu.record.id);},
						scope: this						
					},{
						itemId: "editPerm",
						iconCls: 'ic-people',
						text: t("Edit Permissions"),
						handler: function() {
							var shareWindow = new go.modules.core.core.ShareWindow({
								title: t("Permissions") + ": " + this.moreMenu.record.get('siteName')
							});
							
							shareWindow.load(this.moreMenu.record.get('aclId')).show();
						},
						scope: this							
					},
					{
						itemId: "openFold",
						iconCls: 'ic-folder',
						text: t("Open Folder"),
						handler: function() {console.log("Open folder");},
						scope: this						
					},
					"-"
					,{
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
	},
	
	editProperties : function(id) {
		var dlg = new go.modules.community.pages.SitePropertyDialog();
		dlg.load(id).show();
	}
});

