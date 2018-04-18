go.modules.files.NodeGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'name', 'byteSize', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'permissionLevel'],
			entityStore: go.Stores.get("Folder")
		});

		Ext.apply(this, {
		
			columns: [
				{
					id: 'id',
					hidden: true,
					header: 'ID',
					width: 40,
					sortable: true,
					dataIndex: 'id'
				},
				{
					id: 'name',
					header: t('Name'),
					width: 75,
					sortable: true,
					dataIndex: 'name'
				},
				{
					xtype:"datecolumn",
					id: 'createdAt',
					header: t('Created at'),
					width: 160,
					sortable: true,
					dataIndex: 'createdAt',
					hidden: true
				},
				{					
					xtype:"datecolumn",
					hidden: false,
					id: 'modifiedAt',
					header: t('Modified at'),
					width: 160,
					sortable: true,
					dataIndex: 'modifiedAt'
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>',
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'files-grid'
		});

		go.modules.files.NodeGrid.superclass.initComponent.call(this);
	}
});

