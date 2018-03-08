GO.ipwhitelist.WhitelistGrid = function(config){
	
	if(!config)
	{
		config = {};
	}
	
	config.title = t("Allowed IP addresses", "ipwhitelist");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.store = new GO.data.JsonStore({
		url: GO.url('ipwhitelist/ipAddress/store'),		
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: ['id','ip_address','description','ctime','username','mtime','mUsername'],
		remoteSort: true,
		model:"GO_Ipwhitelist_Model_IpAddress"
	});

	config.store.on('load', function()
	{
		if(config.store.reader.jsonData.feedback)
		{
			alert(config.store.reader.jsonData.feedback);
		}
	},this)

	config.paging=true;

	
	config.columns=[
		{
			header: t("IP address", "ipwhitelist"),
			dataIndex: 'ip_address',
			sortable: true
		},
		{
			header: t("Description"),
			dataIndex: 'description',
			sortable: true
		},{
			header: t("Created at"),
			dataIndex:'ctime',
			hidden:true,
			width: dp(140)
		},
		{
			header: t("User"),
			dataIndex:'username',
			hidden:true,
			width: dp(140)
		},
		{
			header: t("Modified at"),
			dataIndex:'mtime',
			hidden:true,
			width: dp(140)
		},{
			header: t("Modified by"),
			dataIndex:'mUsername',
			hidden:true,
			width: dp(140)
		}
		];
		
	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("No items to display")		
	});
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	this.searchField = new GO.form.SearchField({
		store: config.store,
		width:320
	});
		    	
	config.tbar = [
		{
			grid: this,
			xtype:'addbutton',
			handler: function(b){
				GO.ipwhitelist.showIpAddressDialog(0, this.groupId);
			},
			ignoreButtonParams: true,
			scope: this
		},{
			xtype:'deletebutton',
			grid:this,
			handler: function(){
				this.deleteSelected();
			},
			ignoreButtonParams: true,
			scope: this
		},
		'-',
		t("Search") + ':', this.searchField
	];
	
	GO.ipwhitelist.WhitelistGrid.superclass.constructor.call(this, config);
	
	this.on('rowdblclick',function(grid,rowIndex){
		var record = this.store.getAt(rowIndex);
		GO.ipwhitelist.showIpAddressDialog(record.data['id'],this.groupId);
	}, this);
	
};


Ext.extend(GO.ipwhitelist.WhitelistGrid, GO.grid.GridPanel,{
	
	groupId : 0,
					
	setGroupId : function(groupId) {
		this.groupId = groupId;
		this.store.baseParams['group_id'] = groupId;
	}
	
});

GO.ipwhitelist.showIpAddressDialog = function(addressId,groupId,config){

	if(!GO.ipwhitelist.ipAddressDialog) {
		GO.ipwhitelist.ipAddressDialog = new GO.ipwhitelist.IpAddressDialog();
		GO.ipwhitelist.ipAddressDialog.on('save',function(){
			GO.ipwhitelist.ipGrid.store.load();
		}, this);
	}
	
	GO.ipwhitelist.ipAddressDialog.show(addressId,groupId,config);
}