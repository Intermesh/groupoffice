Ext.define('go.modules.community.history.LogEntryGrid',{
	extend: go.grid.GridPanel,

	autoHeight:true,
	autoExpandColumn: 'name',
	columns:[{
		header: t('ID'),
		width: dp(80),
		dataIndex: 'id',
		hidden:true,
		align: "right"
	},{
		header: t('Name'),
		dataIndex: 'name',
		id: 'name'
	},{
		header: t('Date'),
		dataIndex: 'date',
	},{
		header: t('Description'),
		dataIndex: 'description',
	}],

	initComponent: function() {
		this.store = new go.data.Store({
			fields: ['date','id', 'description',{name: 'creator', type: "relation"}],
			sort: [{property: "date"}],
			entityStore: "LogEntry"
		});

		this.callParent();
		this.store.load();
	}
});