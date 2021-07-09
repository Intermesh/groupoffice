/**
 * Navigation grid
 *
 * See go.modules.community.tasks.TasklistsGrid for an example
 */
go.NavGrid = Ext.extend(go.grid.GridPanel,{
	viewConfig: {
		scrollOffset: 0,
		forceFit: true,
		autoFill: true,
		totalDisplay: false
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
		this.store.on("datachanged", this.onStoreDataChanged, this);
		this.getSelectionModel().on('selectionchange', this.onSelectionChange, this, {buffer: 1}); //add buffer because it clears selection first
	},

	setDefaultSelection : function(selectedListIds) {
		this.filteredStore.setFilter(this.getId(), {[this.filterName]: selectedListIds});
	},

	getSelectedIds: function() {
		const f = this.filteredStore.getFilter(this.getId());
		if(!f) {
			return [];
		}
		return f[this.filterName] || [];
	},

	onStoreDataChanged : function() {
		this.selectAllToolbar.setVisible(this.store.getCount() > 1);
	},

	onStoreLoad: function(store, records, opts) {

		//mark selected records in the filter as seleted in the selection model
		let selected = [], selectedIds = this.getSelectedIds();

		records.forEach((record) => {
			if(selectedIds.indexOf(record.id) > -1) {
				selected.push(record);
			}
		});

		const select = () => {
			this.getSelectionModel().suspendEvents(false)
			this.getSelectionModel().selectRecords(selected, true);
			this.getSelectionModel().resumeEvents();
		}

		if(this.rendered) {
			select();
		} else
		{
			this.on('render', select);
		}


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
		this.fireEvent('beforeshowmenu', this.moreMenu, record);

		this.moreMenu.showAt(e.getXY());
	}
});

Ext.reg('navgrid', go.NavGrid);