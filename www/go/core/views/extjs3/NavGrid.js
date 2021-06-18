

go.NavGrid = Ext.extend(go.grid.GridPanel,{
	viewConfig: {
		scrollOffset: 0,
		forceFit: true,
		autoFill: true
	},
	multiSelectToolbarEnabled : false,
	hideHeaders: true,
	filteredStore: null,
	filterName: null,
	initComponent: function () {

		const actions = this.initRowActions();

		this.plugins = [actions];

		this.selModel = new Ext.grid.CheckboxSelectionModel();

		const tbar = {
			xtype: "container",
			items: [
				{
					items: this.tbar,
					xtype: 'toolbar'
				},
				this.selectAllToolbar = new Ext.Toolbar({
					items: [{xtype: "selectallcheckbox"}]
				})
			]
		};

		this.tbar = tbar;

		if(!this.columns) {
			this.columns = [
				this.selModel,
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
			];
		}

		go.NavGrid.superclass.initComponent.call(this);

		this.store.on("load", this.onStoreLoad, this);
		this.getSelectionModel().on('selectionchange', this.onSelectionChange, this, {buffer: 1}); //add buffer because it clears selection first
	},

	setDefaultSelection : function(selectedListIds) {
		this.filteredStore.setFilter(this.getId(), {[this.filterName]: selectedListIds});
	},

	onStoreLoad: function(store, records, opts) {

		this.selectAllToolbar.setVisible(this.store.getCount() > 1);

		//mark selected records in the filter as seleted in the selection model
		let selected = [], filter = this.filteredStore.getFilter(this.getId())[this.filterName];
		records.forEach((record) => {
			if(filter.indexOf(record.id) > -1) {
				selected.push(record);
			}
		});

		this.getSelectionModel().suspendEvents(false)
		this.getSelectionModel().selectRecords(selected, true);
		this.getSelectionModel().resumeEvents();

	},

	onSelectionChange : function (sm) {
		var ids = [];


		Ext.each(sm.getSelections(), function (r) {
			ids.push(r.id);
		}, this);

		this.filteredStore.setFilter(this.getId(), {[this.filterName]: ids});

		this.fireEvent('selectionchange', ids, sm);

		this.filteredStore.load();
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
				items: this.menuItems
			});
		}

		this.moreMenu.record = record;
		this.fireEvent('beforeshowmenu', this, this.moreMenu, record);

		this.moreMenu.showAt(e.getXY());
	}
});

Ext.reg('navgrid', go.NavGrid);