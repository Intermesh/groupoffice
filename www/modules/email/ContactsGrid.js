GO.email.ContactsGrid = function(config){

	if(!config)
	{
		config = {};
	}

	config.layout='fit';
	config.autoScroll=true;
	config.split=true;	
	config.paging=true;
	config.border=false;

  if(!config.store)
	config.store = new GO.data.JsonStore({
		url : GO.url("addressbook/contact/searchEmail"),
		id : 'email',
		fields : ['id', 'name',  'email', 'ab_name', 'company_name', "function","department"],
		remoteSort : true
	});

	var columnModel =  new Ext.grid.ColumnModel({
		defaults:{
			sortable:true,
			css : 'white-space:normal;'
		},
		columns : [{
			header : t("Name"),
			dataIndex : 'name'			
		}, {
			header : t("E-mail"),
			dataIndex : 'email'			
		},{
			header : t("Company"),
			dataIndex : 'company_name',
			css : 'white-space:normal;',
			sortable : true
		},{
			header : t("Address book", "addressbook"),
			dataIndex : 'ab_name',
			css : 'white-space:normal;',
			sortable : false
		},{
			header : t("Department"),
			dataIndex : 'department'
		},{
			header : t("Function"),
			dataIndex : 'function'
		}]
	});
	config.cm=columnModel;

	config.view=new Ext.grid.GridView({
		autoFill: true,
		forceFit: true,
		emptyText: t("Please enter a search query"),
		deferEmptyText: false
	});
	

	
	config.sm=new Ext.grid.RowSelectionModel({
		singleSelect:config.singleSelect
	});
	
	
	this.contactsSearchField = new GO.form.SearchField({
		store : config.store,
		width : 320
	});
	
	this.contactsSearchField.on("search", function(){
		this.getView().emptyText=t("No items to display");
	}, this);
	
	this.contactsSearchField.on("reset", function(){
		this.getView().emptyText=t("Please enter a search query");
		this.store.removeAll();
		//cancel store load
		return false;
	}, this);
	
	

	config.tbar=[t("Search") + ': ', ' ', this.contactsSearchField];

	GO.email.ContactsGrid.superclass.constructor.call(this, config);

};

Ext.extend(GO.email.ContactsGrid, GO.grid.GridPanel,{

	});
