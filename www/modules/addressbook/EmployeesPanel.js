GO.addressbook.EmployeesPanel = function(config)
	{
		if(!config)
		{
			config={};
		}
	
		config.store = new Ext.data.JsonStore({
			url: GO.url('addressbook/contact/employees'),
			baseParams:
			{
				company_id: this.company_id
//				,task: 'load_employees'
			},
			id:'id',
			root: 'results',
			fields: [
			{
				name:'id'
			},

			{
				name:'name'
			},

			{
				name:'function'
			},

			{
				name:'department'
			},

			{
				name:'work_phone'
			},

			{
				name:'email'
			}
			],
			remoteSort: true
		});
	
		config.store.on('load', function(){
			this.loaded=true;
		}, this);
	
		config.cm =  new Ext.grid.ColumnModel({
			defaults:{
				sortable:true
			},
			columns:[
			{
				header: GO.lang['strName'],
				dataIndex: 'name'
			},
			{
				header: GO.lang['strEmail'],
				dataIndex: 'email' ,
				width: 200
			},
			{
				header: GO.lang['strPhone'],
				dataIndex: 'work_phone' ,
				width: 100
			},
			{
				header: GO.lang['strFunction'],
				dataIndex: 'function',
				width: 150
			},
			{
				header: GO.lang['strDepartment'],
				dataIndex: 'department' ,
				width: 150
			}
			]
		});
        

		config.view=new Ext.grid.GridView({
			autoFill:true,
			forceFit:true
		});
	
		config.layout= 'fit';
		config.paging=true;
		config.title= GO.addressbook.lang['cmdPanelEmployees'];
		config.id= 'ab-employees-grid';
		config.sm= new Ext.grid.RowSelectionModel();
		config.autoScroll=false;
		config.trackMouseOver= true;
		config.collapsible= false;
		config.disabled=true;
  
		config.tbar = [
		{
			iconCls: 'btn-add',
			text: GO.addressbook.lang['addEmployee'],
			cls: 'x-btn-text-icon',
			handler: function () {
				GO.addressbook.showContactDialog(0, {values: {company_id: this.company_id, addressbook_id: this.addressbookId}});
				
				GO.addressbook.contactDialog.on('hide', function() {
					this.store.load();
				}, this, {single: true});
			
			},
			scope: this

		},
		{
			iconCls: 'btn-add',
			text: GO.addressbook.lang['addExistingEmployee'],
			cls: 'x-btn-text-icon',
			handler: function(){
				if(!this.selectContactDialog)
				{
					this.selectContactDialog = new GO.addressbook.SelectContactDialog({
						handler : function(grid){
							var keys = grid.selModel.selections.keys;
							this.store.baseParams.add_contacts = Ext.encode(keys);
							this.store.load();
							delete this.store.baseParams.add_contacts;
						},
						scope: this
					});
				}
				
				var addressbookId = this.addressbookId;
				this.selectContactDialog.grid.store.baseParams.addressbook_id=addressbookId;
				this.selectContactDialog.show({values:{addressbookId: addressbookId, company_id: this.company_id}});
			
			},
			scope: this
		},{

			iconCls: 'btn-delete',
			text: GO.lang['cmdDelete'],
			cls: 'x-btn-text-icon',
			handler: function(){
				this.deleteSelected();
			},
			scope: this
		}];
	
	
  

		GO.addressbook.EmployeesPanel.superclass.constructor.call(this, config);
	
		this.on('rowdblclick', function(grid, index){
			var record = grid.getStore().getAt(index);
			GO.addressbook.showContactDialog(record.data.id);
		}, this);
	}

Ext.extend(GO.addressbook.EmployeesPanel, GO.grid.GridPanel,{
	setCompanyId : function(company_id)
	{
		if(company_id!=this.store.baseParams.company_id)
		{
			this.loaded=false;
                        this.company_id = company_id;
			this.store.baseParams.company_id=company_id;
			this.setDisabled(company_id==0);
		}
	},
	
	setAddressbookId: function(addressbookId) {
		this.addressbookId = addressbookId;
		this.store.baseParams.addressbook_id = addressbookId;
	},
	
	onShow : function(){
		
		if(!this.loaded)
		{
			this.store.load();
		}
		GO.addressbook.CompanyProfilePanel.superclass.onShow.call(this);
	}
});