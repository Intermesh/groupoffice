go.filter.FilterGrid = Ext.extend(go.grid.GridPanel, {
	viewConfig: {
		forceFit: true,
		autoFill: true,
		totalDisplay: false
	},
	autoHeight: true,
	autoScroll:true,
	
	filterStore: null,
	
	entity: null,
	
	hideHeaders: true,

	hidden: true,

	showMoreLoader: true,

	initComponent: function () {

		const actions = this.initRowActions(),
			selModel = new Ext.grid.CheckboxSelectionModel();

		Ext.apply(this, {			
			store: new go.data.Store({
				fields: ['id', 'name', 'aclId', "permissionLevel", "filter"],
				entityStore: "EntityFilter"				
			}).setFilter('base', {
				entity: this.entity,
				type: "fixed"
			}),
			selModel: selModel,
			plugins: [actions],
			columns: [
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
				actions
			]
		});

		go.filter.FilterGrid.superclass.initComponent.call(this);
		
		this.on("render", function() {
			this.store.load();
		}, this);

		this.store.on("load", () => {
			this.setVisible(this.store.getCount() > 0);
		});
		
		
		this.getSelectionModel().on({
			selectionchange: function () {
				this.applyFilters();
			},
			scope: this
		});

		this.store.on("changes", function() {
			this.applyFilters();
		}, this);
		
	},

	applyFilters : function() {
		const selected = this.getSelectionModel().getSelections();

		if(!selected.length) {
			if(this.filterStore) {
				this.filterStore.setFilter("user", undefined);
			}

			this.fire("change", this, undefined);


		} else {
			let filter = {
				operator: "AND",
				conditions: []
			};

			selected.forEach(function(record) {
				filter.conditions.push(record.get('filter'));
			});

			if(this.filterStore) {
				this.filterStore.setFilter("user", filter);
			}

			this.fire("change", this, filter);
		}

		if(this.filterStore)
			this.filterStore.load();
	},

	initRowActions: function () {

		const actions = new Ext.ux.grid.RowActions({
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
							const dlg = new go.filter.FilterDialog({
								entity: this.entity
							});
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
								go.Db.store("EntityFilter").set({destroy: [this.moreMenu.record.id]});
							}, this);
						},
						scope: this						
					}
				]
			});
		}
		
		this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
		this.moreMenu.getComponent("delete").setDisabled(record.get("permissionLevel") < go.permissionLevels.manage);
		
		this.moreMenu.record = record;
		
		this.moreMenu.showAt(e.getXY());
	}
});


Ext.reg('filtergrid', go.filter.FilterGrid);