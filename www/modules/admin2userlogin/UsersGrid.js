GO.admin2userlogin.UsersGrid = function(config){
	
	if(!config)
	{
		config = {};
	}

	var fields ={
		fields : ['id','username','name','lastlogin','ctime','enabled'],
		columns :[{
			header: t("ID", "admin2userlogin"),
			dataIndex: 'id',
			id:'id',
			width:60
		},{
			header: t("Username", "admin2userlogin"),
			dataIndex: 'username',
			id:'username',
			width:180
		},{
			header: t("Switch user", "admin2userlogin"),
			dataIndex: 'name',
			id:'name',
			width:100
		},{
			header: t("Last login attempt", "admin2userlogin"),
			dataIndex: 'lastlogin',
			id:'lastlogin',
			width: dp(140)
		},{
			header: t("Time Registered", "admin2userlogin"),
			dataIndex: 'ctime',
			id:'ctime',
			width: dp(140)
		}]
	};
	
	this.store = new GO.data.JsonStore({
		url: GO.url('core/users'),
		totalProperty:'total',
		fields: fields.fields,
		remoteSort: true
	});
	
	this.searchField = new GO.form.SearchField({
		store: this.store,
		width:320
	});
	
	config.title = t("Switch user", "admin2userlogin");
	config.layout='fit';
	config.autoScroll=true;
	config.split=true;
	config.autoExpandColumn='name';
	config.store = this.store;
	


	config.paging=true;
	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:fields.columns
	});
	
	config.cm=columnModel;
	
	config.view=new Ext.grid.GridView({
		emptyText: t("No items to display"),
		getRowClass : function(record, rowIndex, p, store){
			if(GO.util.empty(record.data.enabled)){
				return 'user-disabled';
			}
		}
	});
	
	config.sm=new Ext.grid.RowSelectionModel();
	config.tbar = new Ext.Toolbar({
		items:[{
		xtype:'htmlcomponent',
		html:t("Switch user", "admin2userlogin"),
		cls:'go-module-title-tbar'
	},t("Search")+': ', ' ',this.searchField],
		cls:'go-head-tb'
	});
	config.loadMask=true;


	Ext.apply(config, {
		listeners:{
			render:function(){
				config.store.load();
			}
		}
	});

	GO.admin2userlogin.UsersGrid.superclass.constructor.call(this, config);
	
	
	
	// dubbelklik, edit bookmark
	this.on('rowdblclick', function(grid, rowIndex){
		var rec = grid.getStore().getAt(rowIndex).data;	
		document.location=GO.url('admin2userlogin/login/switch/',{user_id: rec.id});
	},this)
	
	
};

Ext.extend(GO.admin2userlogin.UsersGrid, GO.grid.GridPanel,{

	});
