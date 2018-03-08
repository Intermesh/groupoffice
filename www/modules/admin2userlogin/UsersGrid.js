GO.admin2userlogin.UsersGrid = function(config){
	
	if(!config)
	{
		config = {};
	}

	var fields ={
		fields : ['id','username','name','lastlogin','ctime','enabled'],
		columns :[{
			header: GO.admin2userlogin.lang.userId,
			dataIndex: 'id',
			id:'id',
			width:60
		},{
			header: GO.admin2userlogin.lang.username,
			dataIndex: 'username',
			id:'username',
			width:180
		},{
			header: GO.admin2userlogin.lang.name,
			dataIndex: 'name',
			id:'name',
			width:100
		},{
			header: GO.admin2userlogin.lang.lastlogin,
			dataIndex: 'lastlogin',
			id:'lastlogin',
			width:110
		},{
			header: GO.admin2userlogin.lang.registrationtime,
			dataIndex: 'ctime',
			id:'ctime',
			width:110
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
	
	config.title = GO.admin2userlogin.lang.admin2userlogin;
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
		emptyText: GO.lang['strNoItems'],
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
		html:GO.admin2userlogin.lang.name,
		cls:'go-module-title-tbar'
	},GO.lang['strSearch']+': ', ' ',this.searchField],
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