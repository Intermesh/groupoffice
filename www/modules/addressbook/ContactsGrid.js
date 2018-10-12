GO.addressbook.ContactsGrid = function(config){
	
	if(!config)
	{
		config = {};
	}

	config.paging=true;
	config.border=false;
	
	var fields ={
		fields : ['id','uuid','name','company_name','first_name','middle_name','last_name','title','initials','sex','birthday','age','email','email2','email3','home_phone','work_phone','work_fax','cellular','cellular2','fax','address','address_no','zip','city','state','country','function','department','salutation','ab_name','ctime','mtime','action_date','suffix','color'],
		columns : [
		{
			header: t("ID", "addressbook"),
			dataIndex: 'id',
			width:20,
			hidden:true
		},{
			header: t("UUID", "addressbook"),
			dataIndex: 'uuid',
			width:50,
			hidden:true
		},{
			id: 'name',
			header: t("Name"), 
			dataIndex: 'name',
			width:300
		},
		{
			header: t("Company"),
			dataIndex: 'company_name',
			width:300
			//sortable:false
		},
		{
			header: t("First name"),
			dataIndex: 'first_name',
			hidden:true
		},
		{
			header: t("Middle name"),
			dataIndex: 'middle_name',
			hidden:true
		},
		{
			header: t("Last name"),
			dataIndex: 'last_name',
			hidden:true
		},
		{
			header: t("Title"),
			dataIndex: 'title',
			hidden:true
		},
		{
			header: t("Initials"),
			dataIndex: 'initials',
			hidden:true
		},{
			header: t("Suffix"),
			dataIndex: 'suffix',
			width:50,
			hidden:true
		},
		{
			header: t("Sex"),
			dataIndex: 'sex',
			hidden:true,
			renderer: function(value,meta){

				if (value === 'M') { 
					meta.css += ' male-cell'; 
					return t("Male", "addressbook"); 
				} 

				if (value === 'F') {	
					meta.css += 'female-cell'; 
					return t("Female", "addressbook");
				}

				return value;
			}
		},
		{
			header: t("Birthday"),
			dataIndex: 'birthday',
			hidden:true
		},{
			header: t("Age"),
			dataIndex: 'age',
			hidden:true
		},
		{
			header: t("E-mail"),
			dataIndex: 'email',
			width: 150,
			hidden:true
		},
		{
			header: t("E-mail") + ' 2',
			dataIndex: 'email2',
			width: 150,
			hidden:true
		},
		{
			header: t("E-mail") + ' 3',
			dataIndex: 'email3',
			width: 150,
			hidden:true
		},
		{
			header: t("Phone"),
			dataIndex: 'home_phone',
			width: 100,
			hidden:true
		},
		{
			header: t("Phone (work)"),
			dataIndex: 'work_phone',
			width: 100,
			hidden:true
		},
		{
			header: t("Fax (work)"),
			dataIndex: 'work_fax',
			width: 100,
			hidden:true
		},
		{
			header: t("Mobile"),
			dataIndex: 'cellular',
			width: 100,
			hidden:true
		},
		{
			header: t("2nd mobile"),
			dataIndex: 'cellular2',
			width: 100,
			hidden:true
		},
		{
			header: t("Fax"),
			dataIndex: 'fax',
			width: 100,
			hidden:true
		},
		{
			header: t("Address"),
			dataIndex: 'address',
			hidden:true
		},
		{
			header: t("Address 2"),
			dataIndex: 'address_no',
			hidden:true
		},
		{
			header: t("ZIP/Postal"),
			dataIndex: 'zip',
			hidden:true
		},
		{
			header: t("City"),
			dataIndex: 'city',
			hidden:true
		},
		{
			header: t("State"),
			dataIndex: 'state',
			hidden:true
		},
		{
			header: t("Country"),
			dataIndex: 'country',
			hidden:true,
			renderer: GO.grid.ColumnRenderers.countryCode
		},
		{
			header: t("Function"),
			dataIndex: 'function',
			hidden:true
		},
		{
			header: t("Department"),
			dataIndex: 'department',
			hidden:true
		},
		{
			header: t("Salutation"),
			dataIndex: 'salutation',
			hidden:true
		},{
			header: t("Address book", "addressbook"),
			dataIndex: 'ab_name',
			hidden:true,
			sortable:true
		},
		{
			xtype: "datecolumn",
			header: t("Modified at"),
			dataIndex:'mtime',
			hidden:true
		},{
			xtype: "datecolumn",
			header: t("Created at"),
			dataIndex:'ctime',
			hidden:true			
		},{
			xtype: "datecolumn",
			dateOnly: true,
			header: t("Action date", "addressbook"),
			dataIndex:'action_date',
			hidden:true
		}
		]
	}
	
	if(go.Modules.isAvailable("core", "customfields"))
		GO.customfields.addColumns("GO\\Addressbook\\Model\\Contact", fields);
	
	config.store = new GO.data.JsonStore({
		url: GO.url('addressbook/contact/store'),
		baseParams: {
			filters:1,
			addresslist_filters:1
		},
		root: 'results',
		id: 'id',
		totalProperty:'total',
		fields: fields.fields,
		remoteSort: true
	});

	config.store.on('load', function()
	{
		if(config.store.reader.jsonData.feedback)
		{
			alert(config.store.reader.jsonData.feedback);
		}
	},this);
	
	config.cm=new Ext.grid.ColumnModel({
		defaults:{
			sortable:true
		},
		columns:fields.columns
	});
	
	config.view=new Ext.grid.GridView({
		emptyText: t("No items to display"),
		getRowClass: function(record, rowIndex, rp, ds){

			if(!rp.tstyle)
				rp.tstyle = '';

			if(!rp.initialstyle)
				rp.initialstyle = rp.tstyle;

			if(record.data.color){				
				rp.tstyle += "color:#"+record.data.color+";";
			} else {
				rp.tstyle= rp.initialstyle;
			}

			return;
		}
	});
					
	config.autoExpandColumn = "name";
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.enableDragDrop=true;
	config.ddGroup='AddressBooksDD';

//		config.bordertrue;
	GO.addressbook.ContactsGrid.superclass.constructor.call(this, config);
	
};


Ext.extend(GO.addressbook.ContactsGrid, GO.grid.GridPanel, {
    applyAddresslistFilters : function()
    {
      this.store.setBaseParam('addresslist_filters', 1);
    }
});
