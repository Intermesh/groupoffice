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
			header: GO.addressbook.lang.id,
			dataIndex: 'id',
			width:20,
			hidden:true
		},{
			header: GO.addressbook.lang.contactUuid,
			dataIndex: 'uuid',
			width:50,
			hidden:true
		},{
			header: GO.lang['strName'], 
			dataIndex: 'name',
			width:200
		},
		{
			header: GO.lang['strCompany'],
			dataIndex: 'company_name',
			width:200
			//sortable:false
		},
		{
			header: GO.lang['strFirstName'],
			dataIndex: 'first_name',
			hidden:true
		},
		{
			header: GO.lang['strMiddleName'],
			dataIndex: 'middle_name',
			hidden:true
		},
		{
			header: GO.lang['strLastName'],
			dataIndex: 'last_name',
			hidden:true
		},
		{
			header: GO.lang['strTitle'],
			dataIndex: 'title',
			hidden:true
		},
		{
			header: GO.lang['strInitials'],
			dataIndex: 'initials',
			hidden:true
		},{
			header: GO.lang['strSuffix'],
			dataIndex: 'suffix',
			width:50,
			hidden:true
		},
		{
			header: GO.lang['strSex'],
			dataIndex: 'sex',
			hidden:true,
			renderer: function(value,meta){

				if (value === 'M') { 
					meta.css += ' male-cell'; 
					return GO.addressbook.lang.male; 
				} 

				if (value === 'F') {	
					meta.css += 'female-cell'; 
					return GO.addressbook.lang.female;
				}

				return value;
			}
		},
		{
			header: GO.lang['strBirthday'],
			dataIndex: 'birthday',
			hidden:true
		},{
			header: GO.lang.age,
			dataIndex: 'age',
			hidden:true
		},
		{
			header: GO.lang['strEmail'],
			dataIndex: 'email',
			width: 150
		},
		{
			header: GO.lang['strEmail'] + ' 2',
			dataIndex: 'email2',
			width: 150,
			hidden:true
		},
		{
			header: GO.lang['strEmail'] + ' 3',
			dataIndex: 'email3',
			width: 150,
			hidden:true
		},
		{
			header: GO.lang['strPhone'],
			dataIndex: 'home_phone',
			width: 100
		},
		{
			header: GO.lang['strWorkPhone'],
			dataIndex: 'work_phone',
			width: 100
		},
		{
			header: GO.lang['strWorkFax'],
			dataIndex: 'work_fax',
			width: 100,
			hidden:true
		},
		{
			header: GO.lang['strCellular'],
			dataIndex: 'cellular',
			width: 100
		},
		{
			header: GO.lang['cellular2'],
			dataIndex: 'cellular2',
			width: 100,
			hidden:true
		},
		{
			header: GO.lang['strFax'],
			dataIndex: 'fax',
			width: 100,
			hidden:true
		},
		{
			header: GO.lang['strAddress'],
			dataIndex: 'address',
			hidden:true
		},
		{
			header: GO.lang['strAddressNo'],
			dataIndex: 'address_no',
			hidden:true
		},
		{
			header: GO.lang['strZip'],
			dataIndex: 'zip',
			hidden:true
		},
		{
			header: GO.lang['strCity'],
			dataIndex: 'city',
			hidden:true
		},
		{
			header: GO.lang['strState'],
			dataIndex: 'state',
			hidden:true
		},
		{
			header: GO.lang['strCountry'],
			dataIndex: 'country',
			hidden:true,
			renderer: GO.grid.ColumnRenderers.countryCode
		},
		{
			header: GO.lang['strFunction'],
			dataIndex: 'function',
			hidden:true
		},
		{
			header: GO.lang['strDepartment'],
			dataIndex: 'department',
			hidden:true
		},
		{
			header: GO.lang['strSalutation'],
			dataIndex: 'salutation',
			hidden:true
		},{
			header: GO.addressbook.lang.addressbook,
			dataIndex: 'ab_name',
			hidden:true,
			sortable:true
		},
		{
			header: GO.lang.strMtime,
			dataIndex:'mtime',
			hidden:true,
			width:110
		},{
			header: GO.lang.strCtime,
			dataIndex:'ctime',
			hidden:true,
			width:110
		},{
			header: GO.addressbook.lang['actionDate'],
			dataIndex:'action_date',
			width:100
		}
		]
	}
	
	if(GO.customfields)
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
		emptyText: GO.lang.strNoItems,
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
	}),
	config.sm=new Ext.grid.RowSelectionModel();
	config.loadMask=true;
	
	config.enableDragDrop=true;
	config.ddGroup='AddressBooksDD';

	config.tbar = config.tbar || [];
	config.tbar.unshift(this.currentActionsButton = new Ext.Button({
			text: GO.addressbook.lang['selectCurrentActions'],
//			disabled: true,
			tooltip: GO.addressbook.lang['showActieveToolTip'],
			enableToggle: true
		}));

//		config.bordertrue;
	GO.addressbook.ContactsGrid.superclass.constructor.call(this, config);
	
	this.currentActionsButton.on('toggle',function(button,pressed){
		this.store.baseParams['onlyCurrentActions'] = pressed ? 1 : 0;
		this.store.load();
	}, this);
	
};


Ext.extend(GO.addressbook.ContactsGrid, GO.grid.GridPanel, {
    applyAddresslistFilters : function()
    {
      this.store.setBaseParam('addresslist_filters', 1);
    }
});
