go.modules.notes.NoteGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'name', 'content', 'excerpt', {name: 'createdAt', type: 'date'}, {name: 'modifiedAt', type: 'date'}, 'permissionLevel'],
			entityStore: go.stores.Note
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
//				enableRowBody: true,
//				showPreview: true,
//				getRowClass: function (record, rowIndex, p, store) {
//					if (this.showPreview) {
//						p.body = '<p>' + record.data.excerpt + '</p>';
//						return 'x-grid3-row-expanded';
//					}
//					return 'x-grid3-row-collapsed';
//				}
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'notes-grid'
		});

		go.modules.notes.NoteGrid.superclass.initComponent.call(this);
	}
});

