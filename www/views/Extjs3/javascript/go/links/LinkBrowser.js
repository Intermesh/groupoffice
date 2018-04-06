
go.links.LinkBrowser = Ext.extend(go.Window, {
	entity: null,
	entityId: null,
	
	stateId: "go-link-browser",

	initComponent: function () {

		var actions = this.initRowActions();


		this.store = new go.data.GroupingStore({
			autoDestroy: true,
			remoteGroup: true,
			fields: ['id', 'toId', 'toEntity', 'to', 'description', {name: 'modifiedAt', type: 'date'}],
			entityStore: go.Stores.get("community", "Link"),
			sortInfo: {field: 'toEntity', direction: 'DESC'},
			autoLoad: true,
			groupOnSort: true,
			groupField: 'toEntity',
			baseParams: {
				filter: [
					{
						entity: this.entity,
						entityId: this.entityId
					}]
			}
		});

		this.grid = new go.grid.GridPanel({
			cls: "go-link-grid",
			region: "center",
			plugins: [actions],
			store: this.store,
			view: new Ext.grid.GroupingView({
				hideGroupedColumn: true,
				forceFit: true,
				// custom grouping text template to display the number of items per group
				groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})'
			}),
			columns: [
				{
					id: 'name',
					header: t('Name'),
					width: 75,
					sortable: true,
					dataIndex: 'name',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {

						var str = record.data.to.name;

						if (rowIndex == 0 || store.getAt(rowIndex - 1).data.toEntity != record.data.toEntity) {
							str = '<i class="entity ' + record.data.toEntity + '"></i>' + str;
						}

						return str;
					}
				}, {
					id: 'toEntity',
					header: t('Type'),
					width: 75,
					sortable: true,
					dataIndex: 'toEntity',
					renderer: function(v) {
						return t(v, go.entities[v].module);
					}
				},
				{
					id: 'modifiedAt',
					header: t('Modified at'),
					width: 160,
					hidden: true,
					sortable: true,
					dataIndex: 'modifiedAt',
					renderer: Ext.util.Format.dateRenderer(go.User.dateTimeFormat)
				},
				actions
			],
			listeners: {
				dblclick: function () {
					var record = this.grid.getSelectionModel().getSelected();
					var entity = go.entities[record.data.toEntity];

					if (!entity) {
						throw record.data.toEntity + " is not a registered entity";
					}
					
					entity.goto(record.data.toId);
					
					this.close();
				},
				scope: this
			},
			autoExpandColumn: 'name'			
		});

		Ext.apply(this, {
			title: t("Links"),
			width: 600,
			height: 600,
			layout: 'border',
			items: [this.grid]
		});

		go.links.CreateLinkWindow.superclass.initComponent.call(this);
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
					iconCls: 'btn-delete ux-row-action-on-hover',
					qtip: t("Add")
				}]
		});

		actions.on({
			action: function (grid, record, action, row, col, e, target) {
				go.Stores.get("community", "Link").set({
					destroy: [record.id]
				});
			}
		});

		return actions;

	}
});


