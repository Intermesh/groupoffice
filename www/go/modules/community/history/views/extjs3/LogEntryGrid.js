Ext.define('go.modules.community.history.LogEntryGrid',{
	extend: go.grid.GridPanel,

	layout:'fit',
	autoExpandColumn: 'name',
	columns:[{
		header: t('ID'),
		width: dp(80),
		dataIndex: 'id',
		hidden:true,
		align: "right"
	},{
		header: t('Name'),
		dataIndex: 'description',
		id: 'name'
	},{
		header: t('Changes'),
		width: dp(80),
		dataIndex: 'changes',
		renderer: function(v, meta, r) {
			var string = [],
				json = JSON.parse(r.data.changes);
			for(var key in json) {
				string.push('<b>'+key+'</b> ' + escape(json[key]).replace(/%20/gm, ' '));
			}
			return '<i class="icon" ext:qtip="'+string.join('<br>')+'">note</i>';
		}
	},{
		header: t('Action'),
		dataIndex: 'action',
		renderer: function(v, meta, r) {
			return t(v.charAt(0).toUpperCase() + v.slice(1));
			//return go.Modules.registered.community.history.actionTypes[v] || 'Unknown';
		}
	},{
		xtype: "datecolumn",
		header: t('Date'),
		dataIndex: 'createdAt',

	},{
		header: t('User'),
		dataIndex: 'creator',
		width:300,
		renderer: function (v) {
			return v ? v.displayName : "-";
		}
	}],

	initComponent: function() {
		this.store = new go.data.Store({
			fields: [{name:'createdAt',type:'date'},'id','action','changes','createdBy', 'description',{name: 'creator', type: "relation"}],
			sort: [{property: "date"}],
			entityStore: "LogEntry"
		});

		this.callParent();
		this.store.load();
	}
});