/* global Ext, go */

go.links.LinkGrid = Ext.extend(go.grid.GridPanel, {

	initComponent: function () {

		this.columns = [
			{
				id: 'name',
				header: t('Name'),
				width: 75,
				sortable: true,
				dataIndex: 'name',
				renderer: function (value, metaData, record, rowIndex, colIndex, store) {
					return '<i class="entity ' + record.data.entity + '"></i> ' + value;
				}
			}, {

				header: t('Type'),
				width: 75,
				sortable: false,
				dataIndex: 'entity',
				hidden: true
			},
			{
				id: 'modifiedAt',
				header: t('Modified at'),
				width: 160,
				sortable: true,
				dataIndex: 'modifiedAt',
				renderer: Ext.util.Format.dateRenderer(go.User.dateTimeFormat),
				hidden: true
			}
		];
		this.autoExpandColumn = 'name';
		this.store = new go.data.Store({
			autoDestroy: true,
			fields: ['id', 'entityId', 'entity', 'name', 'description', {name: 'modifiedAt', type: 'date'}],
			entityStore: "Search",
			sortInfo: {
				field: 'modifiedAt',
				direction: 'DESC'
			},
			baseParams: {
				filter: {}
			}
		});
		
		go.links.LinkGrid.superclass.initComponent.call(this);
	}
});
