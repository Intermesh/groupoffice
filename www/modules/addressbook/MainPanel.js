/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: MainPanel.js 22367 2018-02-13 13:33:32Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
GO.addressbook.MainPanel = function(config) {
	
	config = config || {};
	


		this.exportCompanyMenu = new GO.base.ExportMenuItem({
			disabled: !GO.addressbook.exportPermission,
			className:'GO\\Addressbook\\Export\\CurrentGridCompany', // default
		});
		this.exportContactMenu = new GO.base.ExportMenuItem({
			disabled: !GO.addressbook.exportPermission,
			className:'GO\\Addressbook\\Export\\CurrentGridContact',
		});
		



	GO.addressbook.contactsGrid = this.contactsGrid = new GO.addressbook.ContactsGrid({
		layout: 'fit',
		region: 'center',
		id: 'ab-contacts-grid',
		headerCfg: {cls:'x-hide-display'},
		title: t("Contacts", "addressbook"),
		tbar : {
			enableOverflow: true,
			items:[
			'->',{
				iconCls: 'ic-add',
				tooltip: t('Add'),
				handler: function(){
					this.contactEastPanel.reset();
					GO.addressbook.showContactDialog(0, {values:{addressbook_id:GO.addressbook.defaultAddressbook.get('id')}});
				},
				scope: this
			},{
				iconCls: 'ic-delete',
				hidden:false,
				tooltip: t("Delete"),
				handler: function(){
					this.contactsGrid.deleteSelected({
						callback : this.contactEastPanel.gridDeleteCallback,
						scope: this.contactEastPanel
					});
				},
				scope: this
			},'-',{
				iconCls: 'ic-date-range',//t("Show active", "addressbook"),
				tooltip: t("Show only contacts that have a current action date.", "addressbook"),
				overflowText: t("Show active", "addressbook"),
				enableToggle: true,
				listeners: {
					scope:this,
					toggle: function(button,pressed){
						this.contactsGrid.store.baseParams['onlyCurrentActions'] = pressed ? 1 : 0;
						this.contactsGrid.store.load();
					}
				}
			},{
				xtype: 'tbsearch',
				tools: [{
					text: t("Advanced search", "addressbook"),
					handler: function() {
						if(!this.advancedSearchWindow){
							this.advancedSearchWindow = GO.addressbook.advancedSearchWindow = new GO.addressbook.AdvancedSearchWindow();
						}
						this.advancedSearchWindow.show({
							dataType : 'contacts',
							masterPanel : GO.mainLayout.getModulePanel('addressbook')
						});
					},
					scope: this
				}],
				listeners: {
					search: function(params){
						this.setSearchParams(params);
					},
					scope:this
				}
				//store: this.contactsGrid.store
			},{
					iconCls: 'ic-more-vert',
					menu: [
						{
							iconCls: "ic-settings",
							text: t("Settings"),
							handler: function () {
								var dlg = new GO.addressbook.ManageDialog();
								dlg.show();
							},
							scope: this
						}
						, {
							iconCls: 'ic-merge-type',
							text: t("Newsletters"),
							handler: function () {
								if(!this.mailingStatusWindow)
								{
									this.mailingStatusWindow = new GO.addressbook.MailingStatusWindow();
								}
								return this.mailingStatusWindow.show();
							},
							scope: this,
						},
						this.exportContactMenu

					]
				}
		]},
		listeners: {
			delayedrowselect: function(grid, rowIndex, r){
				this.contactEastPanel.load(r.get('id'));
			},
			rowdblclick: function(){
				this.contactEastPanel.editHandler();
			},
			show: function(){
				this.setAdvancedSearchNotification(this.contactsGrid.store);
				this.addressbooksGrid.setType('contact');
			},
			scope:this
		}
	});
   this.contactsGrid.applyAddresslistFilters();

	this.contactsGrid.store.on('load', function(){
		this.setAdvancedSearchNotification(this.contactsGrid.store);
	}, this);

	if(go.Modules.isAvailable("legacy", "email")) {
		this.contactsGrid.on("rowcontextmenu",function(grid,row,e){
			{
				if(typeof(this.contactsGrid.contextMenu)=='undefined')
				{
					this.contactsGrid.contextMenu = new GO.addressbook.ContextMenu({type:'contact'});
				}
				this.contactsGrid.contextMenu.setSelected(grid, "GO\\Addressbook\\Model\\Contact");
				e.stopEvent();
				this.contactsGrid.contextMenu.showAt(e.getXY());
			}
		},this);
	}

	this.companiesGrid = new GO.addressbook.CompaniesGrid({
		layout: 'fit',
		region: 'center',
		headerCfg: {cls:'x-hide-display'},
		id: 'ab-company-grid',
		title: t("Companies", "addressbook"),
		tbar: ['->', {
			iconCls: 'ic-add',
			tooltip: t("Add"),
			handler: function(){
				this.companyEastPanel.reset();
				GO.addressbook.showCompanyDialog(0,  {values:{addressbook_id:GO.addressbook.defaultAddressbook.get('id')}});
			},
			scope: this
		},{
			iconCls: 'ic-delete',
			tooltip: t("Delete"),
			handler: function(){
				this.companiesGrid.deleteSelected({
					callback : this.companyEastPanel.gridDeleteCallback,
					scope: this.companyEastPanel
				});
			},
			scope: this
		},{
				xtype: 'tbsearch',
				tools: [{
					text: t("Advanced search", "addressbook"),
					handler: function() {
						if(!this.advancedSearchWindow){
							this.advancedSearchWindow = GO.addressbook.advancedSearchWindow = new GO.addressbook.AdvancedSearchWindow();
						}
						this.advancedSearchWindow.show({
							dataType : 'companies',
							masterPanel : GO.mainLayout.getModulePanel('addressbook')
						});
					},
					scope: this
				}],
				listeners: {
					search: function(params){
						this.setSearchParams(params);
					},
					scope:this
				}
        },{
            iconCls: 'ic-more-vert',
            menu: [
                {
                    iconCls: "ic-settings",
                    text: t("Settings"),
                    handler: function () {
                        var dlg = new GO.addressbook.ManageDialog();
                        dlg.show();
                    },
                    scope: this
                }
                , {
                    iconCls: 'ic-merge-type',
                    text: t("Newsletters"),
                    handler: function () {
                        if(!this.mailingStatusWindow)
                        {
                            this.mailingStatusWindow = new GO.addressbook.MailingStatusWindow();
                        }
                        return this.mailingStatusWindow.show();
                    },
                    scope: this,
                },
								this.exportCompanyMenu

            ]
			}
			
		],
		listeners: {
			delayedrowselect: function(grid, rowIndex, r){
				this.companyEastPanel.load(r.get('id'));
			},
			rowdblclick: function(){
				this.companyEastPanel.editHandler();
			},
			show: function(){
				this.setAdvancedSearchNotification(this.companiesGrid.store);
				this.addressbooksGrid.setType('company');
			},
			scope:this
		}
	});
   this.companiesGrid.applyAddresslistFilters();

	if(go.Modules.isAvailable("legacy", "email")) {
		this.companiesGrid.on("rowcontextmenu",function(grid,row,e){
			{
				if(typeof(this.companiesGrid.contextMenu)=='undefined')
				{
					this.companiesGrid.contextMenu = new GO.addressbook.ContextMenu({type:'company'});
				}
				this.companiesGrid.contextMenu.setSelected(grid, "GO\\Addressbook\\Model\\Company");
				e.stopEvent();
				this.companiesGrid.contextMenu.showAt(e.getXY());
			}
		},this);
	}
	
	this.companiesGrid.store.on('load', function(){
		this.setAdvancedSearchNotification(this.companiesGrid.store);
	}, this);

	
		
this.exportCompanyMenu.setColumnModel(this.companiesGrid.getColumnModel());
		this.exportContactMenu.setColumnModel(this.contactsGrid.getColumnModel());

		// Button to export contacts with companies together
		this.contactsWithCompaniesExportButton = new Ext.menu.Item({
			iconCls: 'btn-export',
			text: t("Contacts with companies", "addressbook"),
			handler:function(){
				window.open(GO.url("addressbook/exportContactsWithCompanies/export",{
					viewState: (this.tabPanel.getActiveTab().id == 'ab-contacts') ? 'contact' : 'company'
				}));
			},
			scope: this
		});
		
		this.vcardExportButton = new Ext.menu.Item({
			iconCls: 'btn-export',
			text: t("Export contacts as vcard", "addressbook"),
			handler:function(){
				window.open(GO.url("addressbook/addressbook/exportVCard"));
			},
			scope: this
		});

		this.exportCompanyMenu.insertItem(0,this.vcardExportButton);
		this.exportCompanyMenu.insertItem(0,this.contactsWithCompaniesExportButton);
		this.exportContactMenu.insertItem(0,this.vcardExportButton);
		this.exportContactMenu.insertItem(0,this.contactsWithCompaniesExportButton);
	
	

	this.searchPanel = new GO.addressbook.SearchPanel({
		region: 'north',
		ab:this,
		listeners: {
			queryChange: function(params){
				this.setSearchParams(params);
			},
			scope:this
		}
	});

	GO.addressbook.contactDetail = this.contactEastPanel = this.contactDetail = new GO.addressbook.ContactDetail({ //contactDetail added for routing
		id:'ab-contact-panel',
		border:false,
		region:'east',
		width:dp(504),
		split:true,
		listeners : {
			commentAdded: function(){
				this.contactsGrid.store.reload();
			},
			beforeload: function() {
				
				//make sure contacts is selected when routing
				this.navMenu.select(0);
			},
			scope: this		
		}
	});

	GO.addressbook.companyDetail = this.companyEastPanel = this.companyDetail = new GO.addressbook.CompanyReadPanel({
		id:'ab-company-panel',
		border:false,
		region:'east',
		width:dp(520),
		split:true,
		listeners: {
			beforeload: function() {
				//make sure companies is selected when routing
				this.navMenu.select(1);
			},
			scope: this
		}
	});


	this.addressbooksGrid = new GO.addressbook.AddresbooksGrid({
		autoHeight:true,
		id:'ab-addressbook-grid',
		listeners: {
			change: function(grid, abooks, records){
				var books = Ext.encode(abooks);

				this.companiesGrid.store.baseParams.books = books;
				this.contactsGrid.store.baseParams.books = books;

				if(this.tabPanel.getActiveTab().id == 'ab-contacts'){
					this.contactsGrid.store.load();
					delete this.contactsGrid.store.baseParams.books;
				} else {
					this.companiesGrid.store.load();
					delete this.companiesGrid.store.baseParams.books;
				}

				if(records.length)
				{
					var addressbookIds = [];
					for (var i = 0; i < records.length; i++) {
						addressbookIds.push(records[i].id);
					}
					this.checkForAddressbook(addressbookIds, records);
				}
			},
			scope:this
		}
	});
	
	this.addressbooksGrid.getStore().on('load', function (store, records, options) {
		var addressbookIds = [];
		for (var i=0; i<records.length; i++) {
			if(records[i].get('checked')){
				addressbookIds.push(records[i].id);
			}
		}
		
		this.checkForAddressbook(addressbookIds, records);
	}, this);

	/*
	this.addressbooksGrid.getSelectionModel().on('rowselect', function(sm, rowIndex, r){
		GO.addressbook.defaultAddressbook = sm.getSelected().get('id');

		var record = this.addressbooksGrid.getStore().getAt(rowIndex);
		this.setSearchParams({addressbook_id : record.get("id")});
	}, this);
	*/


	this.addressbooksGrid.on('drop', function(type)
	{
		if(type == 'company')
		{
			this.companiesGrid.store.reload();
		}else
		{
			this.contactsGrid.store.reload();
		}
	}, this);

	this.tabPanel = new Ext.TabPanel({
		activeTab: 0,
		headerCfg: {cls:'x-hide-display'},
		listeners:{
			scope:this,
			tabchange:function(tabPanel, activeTab){
				if(activeTab.id=='ab-contacts')
					this.contactsGrid.store.load();
				else
					this.companiesGrid.store.load();
			}
		},
		items: [{
			id:'ab-contacts',
			layout: 'border',
			items:[
				this.contactsGrid,
				this.contactEastPanel
			]
		},{
			id: 'ab-companies',
			layout: 'border',
			items:[
				this.companiesGrid,
				this.companyEastPanel
			]
		}
		]
	});

	config.layout='border';
	config.border=false;

	this.mailingsFilterPanel= new GO.addressbook.AddresslistsGroupedMultiSelectGrid({
		id: 'ab-mailingsfilter-panel',
		region:'center',
		split:true
	});

	this.mailingsFilterPanel.getStore().load();

	this.mailingsFilterPanel.on('change', function(grid, addresslist_filter){
		var panel = this.tabPanel.getActiveTab();
		if(panel.id=='ab-contacts-grid')
		{
			this.contactsGrid.store.baseParams.addresslist_filter = Ext.encode(addresslist_filter);
			this.contactsGrid.store.load();
			//delete this.contactsGrid.store.baseParams.addresslist_filter;
		}else
		{
			this.companiesGrid.store.baseParams.addresslist_filter = Ext.encode(addresslist_filter);
			this.companiesGrid.store.load();
			//delete this.companiesGrid.store.baseParams.addresslist_filter;
		}
	}, this);

	this.westPanel = new Ext.Panel({
		//layout:'accordion',
		//layoutConfig:{hideCollapseTool:true},
		border:false,
		split:true,
		autoHeight:true,
		items:[this.addressbooksGrid],
		id: 'ab-west-panel'
	});

	//This is an accordion panel only for the favorites module. If there's only
	//one item then this will disable the collapsing.
	this.addressbooksGrid.on('beforecollapse',function(){
		if(this.westPanel.items.getCount()==1){
			return false;
		}
	}, this);

	this.navMenu = new go.NavMenu({
		region:'north',
		store: new Ext.data.ArrayStore({
			fields: ['name', 'icon', 'visible'],
			data: [
				['Contacts', 'person'],
				['Companies', 'domain']
//				[t("Newsletters", "addressbook"), 'email', !!GO.email],
//				['Settings', 'settings',GO.addressbook.permission_level == 50]
			]
		}),
		listeners: {
			selectionchange: function(view, nodes) {					
				if(nodes[0].viewIndex == 3) {
					return this.tabPanel.setActiveTab(2);
				}				
				this.tabPanel.setActiveTab(nodes[0].viewIndex);
			},
			scope: this
		}
	});

	this.westPanelContainer = new Ext.Panel({
		region:'west',
		cls: 'go-sidenav',
		width:dp(224),
		autoScroll:true,
		split:true,
		items: [this.navMenu, this.westPanel,this.mailingsFilterPanel]			
	});

	
	config.items= [
		//this.searchPanel,
		this.westPanelContainer,
		new Ext.Panel({
			layout: 'fit',
			region : 'center',
			border: true,
			items: [this.tabPanel]
		})
	];

	GO.addressbook.MainPanel.superclass.constructor.call(this, config);

};

Ext.extend(GO.addressbook.MainPanel, Ext.Panel,{

	setAdvancedSearchNotification : function (store)
	{
		if(!GO.util.empty(store.baseParams.advancedQueryData))
		{
			this.searchPanel.queryField.setValue("[ "+t("Advanced search", "addressbook")+" ]");
			this.searchPanel.queryField.setDisabled(true);
		}else
		{
			if(this.searchPanel.queryField.getValue()=="[ "+t("Advanced search", "addressbook")+" ]")
			{
				this.searchPanel.queryField.setValue("");
			}
			this.searchPanel.queryField.setDisabled(false);
		}
	},

	init : function(){
		
		
		this.getEl().mask(t("Loading..."));
		GO.request({
			maskEl:this.getEl(),
			url: "core/multiRequest",
			params:{
				requests:Ext.encode({
//					contacts:{r:"addressbook/contact/store"},
//					companies:{r:"addressbook/company/store"},
					addressbooks:{r:"addressbook/addressbook/store", limit: 0},
					writable_addresslists:{r:"addressbook/addresslist/store",permissionLevel: GO.permissionLevels.write, limit: 0},
					readable_addresslists:{r:"addressbook/addresslist/store",permissionLevel: GO.permissionLevels.read, limit: GO.settings.config.nav_page_size}
				})
			},
			success: function(options, response, result)
			{
				GO.addressbook.readableAddressbooksStore.loadData(result.addressbooks);
//				this.contactsGrid.store.loadData(result.contacts);
//				if(go.Modules.isAvailable("legacy", "addressbook"))
//				{
					GO.addressbook.readableAddresslistsStore.loadData(result.readable_addresslists);
					GO.addressbook.writableAddresslistsStore.loadData(result.writable_addresslists);
//				}
				if(this.navMenu.getSelectionCount() == 0) {
					this.navMenu.select(0);
				}
				this.getEl().unmask();
			},
			scope:this
		});
	},

	afterRender : function()
	{
		GO.addressbook.MainPanel.superclass.afterRender.call(this);

		this.init();

		GO.dialogListeners.add('contact',{
			scope:this,
			save:function(){

				var panel = this.tabPanel.getActiveTab();
				console.log(panel.id);
				if(panel.id=='ab-contacts')
				{
					this.contactsGrid.store.reload();
				}
			}
		});

		GO.dialogListeners.add('company',{
			scope:this,
			save:function(){
				var panel = this.tabPanel.getActiveTab();
				console.log(panel.id);
				if(panel.id=='ab-companies')
				{
					this.companiesGrid.store.reload();
				}
			}
		});
	},

	checkForAddressbook: function (addressbookIds, records) {
		
			GO.request({
				url: 'addressbook/addressbook/firstWritableAddressbookId',
				params: {
					addressbook_ids : Ext.encode(addressbookIds)
				},
				success: function(response,options,result) {
					for (var i=0; i<records.length; i++) {
						if (records[i].id==result.data.addressbook_id)
							GO.addressbook.defaultAddressbook = records[i];
						
						
					}
					if(!GO.addressbook.defaultAddressbook) {
						GO.addressbook.defaultAddressbook = records[0];
					}
				},
				scope: this
			});
	},
	setSearchParams : function(params)
	{
		var panel = this.tabPanel.getActiveTab();

		for(var name in params)
		{
			if(name!='advancedQuery' || panel.id=='ab-contacts-grid')
			{
				this.contactsGrid.store.baseParams[name] = params[name];
			}
			if(name!='advancedQuery' || panel.id!='ab-contacts-grid')
			{
				this.companiesGrid.store.baseParams[name] = params[name];
			}
		}

		if(panel.id=='ab-contacts-grid')
		{
			this.contactsGrid.store.load();
		}else
		{
			this.companiesGrid.store.load();
		}
	},
					
	_createExportMenuItems : function() {
		
		// Button to export contacts with companies together
		this.contactsWithCompaniesExportButton = new Ext.menu.Item({
			iconCls: 'btn-export',
			text: t("Contacts with companies", "addressbook"),
			cls: 'x-btn-text-icon',
			handler:function(){
				window.open(GO.url("addressbook/exportContactsWithCompanies/export"))
			},
			scope: this
		});
		

							
	}
});

GO.addressbook.showContactDialog = function(contact_id, config){

	if(!GO.addressbook.contactDialog)
		GO.addressbook.contactDialog = new GO.addressbook.ContactDialog();

	if(GO.addressbook.contactDialogListeners){
		GO.addressbook.contactDialog.on(GO.addressbook.contactDialogListeners);
		delete GO.addressbook.contactDialogListeners;
	}
		
	GO.addressbook.contactDialog.show(contact_id, config);
}

GO.addressbook.showCompanyDialog = function(company_id, config){

	if(!GO.addressbook.companyDialog)
		GO.addressbook.companyDialog = new GO.addressbook.CompanyDialog();

	if(GO.addressbook.companyDialogListeners){
		GO.addressbook.companyDialog.on(GO.addressbook.companyDialogListeners);
		delete GO.addressbook.companyDialogListeners;
	}
	if(!config) {
		config = {};
	}
	GO.addressbook.companyDialog.show(company_id, config);
}

GO.addressbook.searchSenderStore = new GO.data.JsonStore({
	url: GO.url('addressbook/addressbook/searchSender'),
	baseParams: {
		email:''
	},
	root: 'results',
	totalProperty: 'total',
	id: 'id',
	fields:['id','name'],
	remoteSort:true
});

GO.addressbook.searchSender = function(sender, name){
	GO.addressbook.searchSenderStore.baseParams.email=sender;
	GO.addressbook.searchSenderStore.load({
		callback:function(){
			switch(GO.addressbook.searchSenderStore.getCount())
			{
				case 0:
					var names = name.split(' ');
					var params = {
						email:sender,
						first_name: names[0]
					};

					if(names[2])
					{
						params.last_name=names[2];
						params.middle_name=names[1];
					}else if(names[1])
					{
						params.middle_name='';
						params.last_name=names[1];
					}

					if(!GO.addressbook.unknownEmailWin)
					{
						GO.addressbook.unknownEmailWin=new GO.Window({
							title:t("Unknown email address", "addressbook"),
							items:{
								autoScroll:true,
								items: [{
									xtype: 'plainfield',
									hideLabel: true,
									value: t("This email address is unknown. Do want to add this to a new contact or an existing contact?", "addressbook")
								}],
								cls:'go-form-panel'
							},
							layout:'fit',
							autoScroll:true,
							closeAction:'hide',
							closeable:true,
							height:160,
							width:400,
							buttons:[{
								text: t("New Contact", "addressbook"),
								handler: function(){
									GO.addressbook.showContactDialog(0, {values:GO.addressbook.unknownEmailWin.params});
									GO.addressbook.unknownEmailWin.hide();
								}
							},{
								text: t("Existing Contact", "addressbook"),
								handler: function(){
									if(!GO.email.findContactDialog)
										GO.email.findContactDialog = new GO.email.FindContactDialog();

									GO.email.findContactDialog.show(GO.addressbook.unknownEmailWin.params);
									GO.addressbook.unknownEmailWin.hide();
								}
							},{
								text: t("Cancel"),
								handler: function(){
									GO.addressbook.unknownEmailWin.hide();
								}
							}],
						scope: this
						});
					}
					GO.addressbook.unknownEmailWin.params=params;
					GO.addressbook.unknownEmailWin.show();

					break;
				case 1:
					var r = GO.addressbook.searchSenderStore.getAt(0);
					go.Router.goto("contact/" + r.get('id'));
					break;
				default:
					if(!GO.addressbook.searchSenderWin)
					{
						var list = new GO.grid.SimpleSelectList({
							store: GO.addressbook.searchSenderStore
						});

						list.on('click', function(dataview, index){
							var contact_id = dataview.store.data.items[index].id;
							list.clearSelections();
							GO.addressbook.searchSenderWin.hide();
							go.Router.goto("contact/" + contact_id);
						}, this);
						GO.addressbook.searchSenderWin=new GO.Window({
							title:t("Select contact", "addressbook"),
							items:{
								autoScroll:true,
								items: list,
								cls:'go-form-panel'
							},
							layout:'fit',
							autoScroll:true,
							closeAction:'hide',
							closeable:true,
							height:400,
							width:400,
							buttons:[{
								text: t("Close"),
								handler: function(){
									GO.addressbook.searchSenderWin.hide();
								}
							}]
						});
					}
					GO.addressbook.searchSenderWin.show();
					break;
			}
		},
		scope:this
	});

}



go.Modules.register("legacy", 'addressbook', {
	mainPanel: GO.addressbook.MainPanel,
	title: t("Address book", "addressbook"),
	iconCls: 'go-tab-icon-addressbook',
	entities: ["Contact", "Company"],
	userSettingsPanels: ["GO.addressbook.SettingsPanel"],
	initModule: function () {	
		go.Links.registerLinkToWindow("Contact", function() {
			var win = new GO.addressbook.ContactDialog ();
			win.closeAction = "hide";
			return win;
		});
		
		go.Links.registerLinkToWindow("Company", function() {
			var win = new GO.addressbook.CompanyDialog ();
			win.closeAction = "hide";
			return win;
		});
		
		GO.addressbook.addressbooksStoreFields = new Array('id','name','user_name', 'acl_id','user_id','contactCustomfields','companyCustomfields','default_salutation', 'checked');


GO.addressbook.readableAddressbooksStore = new GO.data.JsonStore({
			url: GO.url('addressbook/addressbook/store'),
			baseParams: {
				'permissionLevel' : GO.permissionLevels.read,
				limit:parseInt(GO.settings['max_rows_list'])

				},
			root: 'results', 
			totalProperty: 'total', 
			id: 'id',
			fields: GO.addressbook.addressbooksStoreFields,
			remoteSort: true
		});

GO.addressbook.writableAddressbooksStore = new GO.data.JsonStore({
			url: GO.url('addressbook/addressbook/store'),
			baseParams: {
				'permissionLevel' : GO.permissionLevels.write,
				limit:parseInt(GO.settings['max_rows_list'])
				},
			root: 'results', 
			totalProperty: 'total', 
			id: 'id',
			fields: GO.addressbook.addressbooksStoreFields,
			remoteSort: true
		});

GO.addressbook.writableAddresslistsStore = new GO.data.JsonStore({
    url: GO.url("addressbook/addresslist/store"),
    baseParams: {
        permissionLevel: GO.permissionLevels.write,
				limit:0
    },
    fields: ['id', 'name', 'user_name','acl_id'],
    remoteSort: true
});
		
GO.addressbook.writableAddresslistsStore.on('load', function(){
	GO.addressbook.writableAddresslistsStore.on('load', function(){
    GO.addressbook.readableAddresslistsStore.load();
	}, this);
}, this, {single:true});
		
GO.addressbook.readableAddresslistsStore = new GO.data.JsonStore({
    url: GO.url("addressbook/addresslist/store"),
    baseParams: {
        permissionLevel: GO.permissionLevels.read
    },
    fields: ['id', 'name', 'user_name','acl_id', 'checked'],
    remoteSort: true
});

	}
});

//
//GO.linkHandlers["GO\\Addressbook\\Model\\Contact"]=GO.mailFunctions.showContact=GO.addressbook.showContact=function(id){
//	if(!GO.addressbook.linkContactWindow){
//		var contactPanel = new GO.addressbook.ContactReadPanel();
//		GO.addressbook.linkContactWindow = new GO.LinkViewWindow({
//			title: t("Contact", "addressbook"),
//			items: contactPanel,
//			contactPanel: contactPanel,
//			closeAction:"hide"
//		});
//	}
//	GO.addressbook.linkContactWindow.contactPanel.load(id);
//	GO.addressbook.linkContactWindow.show();
//	return GO.addressbook.linkContactWindow;
//}
//
//GO.linkPreviewPanels["GO\\Addressbook\\Model\\Contact"]=function(config){
//	config = config || {};
//	return new GO.addressbook.ContactReadPanel(config);
//}
//
//GO.linkPreviewPanels["GO\\Addressbook\\Model\\Company"]=function(config){
//	config = config || {};
//	return new GO.addressbook.CompanyReadPanel(config);
//}
//
//
//GO.linkHandlers["GO\\Addressbook\\Model\\Company"]=function(id){
//
//	if(!GO.addressbook.linkCompanyWindow){
//		var companyPanel = new GO.addressbook.CompanyReadPanel();
//		GO.addressbook.linkCompanyWindow = new GO.LinkViewWindow({
//			title: t("Company", "addressbook"),
//			items: companyPanel,
//			companyPanel: companyPanel,
//			closeAction:"hide"
//		});
//	}
//	GO.addressbook.linkCompanyWindow.companyPanel.load(id);
//	GO.addressbook.linkCompanyWindow.show();
//	return GO.addressbook.linkCompanyWindow;
//}
//
//GO.linkHandlers["GO\\Addressbook\\Model\\Addresslist"]=function(id){}
//
//GO.quickAddPanel.addButton(new Ext.Button({
//	iconCls:'img-contact-add',
//	cls: 'x-btn-icon', 
//	tooltip:t("Contact", "addressbook"),
//	handler:function(item, e){
//		GO.addressbook.showContactDialog(0,{});
//	},
//	scope: this
//}),0);
