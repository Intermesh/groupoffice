go.modules.community.music.GenreFilter = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true
	},
	
	//This component is going to be the side navigation
	cls: 'go-sidenav', 

	constructor: function (config) {
		
		// Good practice to initialize config if not given
		config = config || {};

		// Row actions is a special grid column with an actions menu in it.
		var actions = this.initRowActions();

		// A selection model with checkboxes in this filter.
		var selModel = new Ext.grid.CheckboxSelectionModel();
		
		// A toolbar that consists out of two rows.
		var tbar = {
			xtype: "container",
			items:[
				{
					items: config.tbar || [], 
					xtype: 'toolbar'
				},
				new Ext.Toolbar({
					items:[{xtype: "selectallcheckbox"}]
				})
			]
		};

		Ext.apply(config, {
			
			tbar: tbar,
			
			// We use a "go.data.Store" that connects with an Entity store. This store updates automaticaly when entities change.
			store: new go.data.Store({
				fields: ['id', 'name', 'aclId', "permissionLevel"],
				entityStore: go.Stores.get("Genre")
			}),
			selModel: selModel,
			plugins: [actions],
			columns: [
				// The checkbox selection model must be added as a column too
				selModel,
				{
					id: 'name',
					header: t('Name'),
					sortable: false,
					dataIndex: 'name',
					hideable: false,
					draggable: false,
					menuDisabled: true
				},
				// The actions column showing a menu with delete and edit items.
				actions
			],

			// Change to true to remember the state of the panel
			stateful: false,
			stateId: 'music-genre-filter'
		});

		go.modules.community.music.GenreFilter.superclass.constructor.call(this, config);
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
						// We use Material design icons. Look them up at https://material.io/tools/icons/?style=baseline. You can use ic-{name} as class names.
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function() {
							var dlg = new go.modules.community.music.GenreForm();
							dlg.load(this.moreMenu.record.id).show();
						},
						scope: this						
					},{
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function() {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn != "yes") {
									return;
								}
								go.Stores.get("Genre").set({destroy: [this.moreMenu.record.id]});
							}, this);
						},
						scope: this						
					}
				]
			})
		}
		
		this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		this.moreMenu.getComponent("delete").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		
		this.moreMenu.record = record;
		
		this.moreMenu.showAt(e.getXY());
	}
});

