Ext.define('go.modules.community.oauth2client.ClientGrid',{
	extend: go.grid.GridPanel,

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
	}],

	initComponent: function() {
		this.store = new go.data.Store({
			fields: ['name','id'],
			sort: [{property: "name"}],
			entityStore: "Oauth2Client"
		});
		this.tbar = [{
			xtype: 'tbtitle',
			text: t("OAuth2 Connections", 'oauth2client', 'community')
		},'->',{
			iconCls: "ic-add",
			handler: function() {
				var dlg = new go.modules.community.oauth2client.ClientDialog();
				dlg.show();
			},scope: this
		}];
		this.listeners = {
			rowdblclick: function (grid, rowIndex, e) {
				if(rights.mayChangeCompanies) {
					var record = grid.getStore().getAt(rowIndex);
					var dlg = new go.modules.community.oauth2client.ClientDialog();
					dlg.load(record.id).show();
				}
			},
			scope:this
		};

		this.callParent();
		this.store.load();

	}
});