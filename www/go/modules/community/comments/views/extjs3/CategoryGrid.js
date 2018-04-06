go.modules.comments.CategoryGrid = Ext.extend(go.grid.GridPanel, {
	paging: true,
	initComponent: function () {

		this.store = new go.data.Store({
			fields: ['id', 'name'],
			entityStore: go.Stores.get("CommentCategory")
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
				}
			],
			viewConfig: {
				emptyText: 	'<i>description</i><p>' +t("No items to display") + '</p>'
			},
			autoExpandColumn: 'name',
			// config options for stateful behavior
			stateful: true,
			stateId: 'comments-catetory-grid'
		});

		go.modules.comments.CategoryGrid.superclass.initComponent.call(this);
	}
});

