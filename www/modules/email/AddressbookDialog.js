/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: AddressbookDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 * 
 * 
 * Params:
 * 
 * linksStore: store to reload after items are linked gridRecords: records from
 * grid to link. They must have a link_id and link_type fromLinks: array with
 * link_id and link_type to link
 */

/**
 * @class GO.dialog.SelectEmail
 * @extends go.Window A window to select a number of User-Office user Users.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is
 *      clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object}
 *            config The config object
 */

GO.email.AddressbookDialog = function(config) {

	Ext.apply(this, config);

	var items = Array();
	
	if(go.Modules.isAvailable("legacy", "addressbook")) {
		this.contactsGrid = new GO.email.ContactsGrid({
			title:t("Contacts", "addressbook"),
			id: 'em-contacts-grid-tab'
		});

//		this.contactsGrid.on('show', function() {			
//			//this.contactsGrid.store.load();
//		}, this);

		this.companiesStore = new GO.data.JsonStore({
			url : GO.url("addressbook/company/store"),
			baseParams : {
				//task : 'companies',
				require_email:true				
			},
//			root : 'results',
//			id : 'id',
//			totalProperty : 'total',
			fields : ['id', 'name', 'city', 'email', 'phone',
			'homepage', 'address', 'zip'],
			remoteSort : true
		});

		this.companySearchField = new GO.form.SearchField({
			store : this.companiesStore,
			width : 320
		});
		
		this.companySearchField.on("search", function(){
			this.companyGrid.getView().emptyText=t("No items to display");
		}, this);

		this.companySearchField.on("reset", function(){
			this.companyGrid.getView().emptyText=t("Please enter a search query");
			
			this.companyGrid.store.removeAll();
			//cancel store load
			return false;
		}, this);

		this.companyGrid = new GO.grid.GridPanel({
			title : t("Companies", "addressbook"),
			id: 'em-companies-grid-tab',
			paging : true,
			border : false,
			store : this.companiesStore,
			view : new Ext.grid.GridView({
				autoFill : true,
				forceFit : true,
				deferEmptyText: false,
				emptyText: t("Please enter a search query")
			}),
			columns : [{
				header : t("Name"),
				dataIndex : 'name',
				css : 'white-space:normal;',
				sortable : true
			}, {
				header : t("E-mail"),
				dataIndex : 'email',
				css : 'white-space:normal;',
				sortable : true
			}],
			sm : new Ext.grid.RowSelectionModel(),
			tbar : [t("Search") + ': ', ' ',
			this.companySearchField]
		});

//		this.companyGrid.on('show', function() {
//			this.companiesStore.load();
//		}, this);

		items.push(this.contactsGrid);
		items.push(this.companyGrid);

	}
		
	
	this.usersStore = new GO.data.JsonStore({
		url : GO.url("core/users"),
		baseParams:{
			queryRequired:true
		},
		fields : ['id', 'username', 'name',  'email'],
		remoteSort : true
	});

	this.usersSearchField = new GO.form.SearchField({
		store : this.usersStore,
		width : 320
	});
	
	this.usersSearchField.on("search", function(){
		this.usersGrid.getView().emptyText=t("No items to display");
	}, this);

	this.usersSearchField.on("reset", function(){
		this.usersGrid.getView().emptyText=t("Please enter a search query");
		
		this.usersGrid.store.removeAll();
		//cancel store load
		return false;
	}, this);

	this.usersGrid = new GO.grid.GridPanel({
		title : t("Users", "addressbook"),
		id: 'em-users-grid-tab',
		paging : true,
		border : false,
		store : this.usersStore,
		view : new Ext.grid.GridView({
			autoFill : true,
			forceFit : true,
			deferEmptyText: false,
			emptyText: t("Please enter a search query")
		}),
		columns : [{
			header : t("Name"),
			dataIndex : 'name',
			css : 'white-space:normal;',
			sortable : true
		}, {
			header : t("Username"),
			dataIndex : 'username',
			css : 'white-space:normal;',
			sortable : true
		},{
			header : t("E-mail"),
			dataIndex : 'email',
			css : 'white-space:normal;',
			sortable : true
		}],
		sm : new Ext.grid.RowSelectionModel(),
		tbar : [t("Search") + ': ', ' ', this.usersSearchField]
	});

//	this.usersGrid.on('show', function() {
//		this.usersStore.load();
//	}, this);
	items.push(this.usersGrid);
	
	

	if(go.Modules.isAvailable("legacy", "addressbook")) {
		
		this.mailingsStore = new Ext.data.GroupingStore({
			reader: new Ext.data.JsonReader({
				totalProperty: "total",
				root: "results",
				id: "id",
				fields: ['id', 'name', 'user_name','acl_id', 'checked','addresslistGroupName']
			}),
			baseParams: {
				permissionLevel: GO.permissionLevels.read,
				limit:GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):parseInt(GO.settings['max_rows_list'])
			},
			proxy: new Ext.data.HttpProxy({
				url:GO.url('addressbook/addresslist/store')
			}),        
			groupField:'addresslistGroupName',
			remoteSort:true,
			remoteGroup:true
		});
		
		this.mailingsSearchField = new GO.form.SearchField({
			store : this.mailingsStore,
			width : 320
		});

//		this.mailingsSearchField.on("search", function(){
//			this.mailingsGrid.getView().emptyText=t("No items to display");
//		}, this);
//
//		this.mailingsSearchField.on("reset", function(){
//			this.mailingsGrid.getView().emptyText=t("Please enter a search query");
//
//			this.mailingsGrid.store.removeAll();
//			//cancel store load
//			return false;
//		}, this);

		this.mailingsGrid = new GO.grid.GridPanel({
			title : t("Address lists", "addressbook"),
			id: 'em-addresslists-grid-tab',
			paging : GO.settings.addresslists_store_forced_limit?parseInt(GO.settings.addresslists_store_forced_limit):true,
			border : false,
			store : this.mailingsStore,
			view:new Ext.grid.GroupingView({
				autoFill:true,
				forceFit:true,
		    hideGroupedColumn:true,
		    groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "Items" : "Item"]})',
		   	emptyText: t("No items found"),
		   	showGroupName:false,
				startCollapsed:true
			}),
			columns : [{
				header : t("Name"),
				dataIndex : 'name',
				css : 'white-space:normal;',
				sortable : true
			},{
				header: t("Addresslist group", "addressbook"),
				dataIndex: 'addresslistGroupName'
			}],
			sm : new Ext.grid.RowSelectionModel(),
			tbar : [t("Search") + ': ', ' ', this.mailingsSearchField]
		});
		this.mailingsGrid.on('show', function() {
			if(!GO.addressbook.readableAddresslistsStore.loaded)
				GO.addressbook.readableAddresslistsStore.load();
		}, this);
		
		this.mailingsGrid.on('show', function() {			
			this.mailingsGrid.store.load();
		}, this);

		items.push(this.mailingsGrid);
	}
	
	/*
	 * this.usersGrid.on('afterRender', function(){
	 * if(this.usersGrid.isVisible()) { this.onShow(); } }, this);
	 */

	this.userGroupsStore = new GO.data.JsonStore({
		url : GO.url('core/groups'),
		baseParams : {
			for_mail : 1
		},
		id : 'id',
		root : 'results',
		fields: ['id', 'name', 'user_id', 'user_name'],
		totalProperty : 'total',
		remoteSort : true
	});

	this.userGroupsGrid = new GO.grid.GridPanel({
		title : t("Groups", "email"),
			id: 'em-usergroups-grid-tab',
		paging : true,
		border : false,
		store : this.userGroupsStore,
		tbar : [t("Search") + ': ', ' ',
			new GO.form.SearchField({
			store : this.userGroupsStore,
			width : 320
		})],
		view : new Ext.grid.GridView({
			autoFill : true,
			forceFit : true
		}),
		columns : [{
			header : t("Name"),
			dataIndex : 'name',
			css : 'white-space:normal;',
			sortable : true
		}, {
			header : t("Owner"),
			dataIndex : 'user_name',
			css : 'white-space:normal;',
			sortable : true
		}],
		sm : new Ext.grid.RowSelectionModel()
	});

	this.userGroupsGrid.on('show', function() {
		this.userGroupsStore.load();
	}, this);
	
	items.push(this.userGroupsGrid);

	

	this.tabPanel = new Ext.TabPanel({
		activeTab : 0,
		items : items,
		border : false
	});

	GO.email.AddressbookDialog.superclass.constructor.call(this, {
		layout : 'fit',
		modal : false,
		height : 400,
		width : 600,
		closeAction : 'hide',
		title : t("Address book", "addressbook"),
		items : this.tabPanel,
		buttons : [{
			text : t("Add to recipients", "email"),
			handler : function() {
				this.addRecipients('to');
			},
			scope : this
		}, {
			text : t("Add to CC", "email"),
			handler : function() {
				this.addRecipients('cc');
			},
			scope : this
		}, {
			text : t("Add to BCC", "email"),
			handler : function() {
				this.addRecipients('bcc');
			},
			scope : this
		}, {
			text : t("Close"),
			handler : function() {
				this.hide();
			},
			scope : this
		}]
	});

	this.addEvents({
		addrecipients : true
	});
};

Ext.extend(GO.email.AddressbookDialog, GO.Window, {
	focus : function(){
		var panel = this.tabPanel.getActiveTab();
		var tb = panel.getTopToolbar();
		if(tb){
			var components =tb.findByType("searchfield");
			if(components.length)
				components[0].focus();
		}
		
	},
	addRecipients : function(field) {
		var str="";
		var activeGrid = this.tabPanel.getLayout().activeItem;
		var selections = activeGrid.selModel.getSelections();
				
		if (this.mailingsGrid && activeGrid == this.mailingsGrid) {
					
			var addresslists = [];
					
			for(var i=0;i<selections.length;i++)
			{
				addresslists.push(selections[i].data.id);
			}					

			GO.request({
				maskEl: this.getEl(),
				url: "addressbook/addresslist/getRecipientsAsString",
				params: {					
					addresslists: Ext.encode(addresslists)
				},
				success: function(options, response, result)
				{					
					this.fireEvent('addrecipients', field, result.recipients);
				},
				scope:this
			});

		}else
		if(activeGrid == this.userGroupsGrid)
		{
			var user_groups = [];

			for(var i=0;i<selections.length;i++)
			{
				user_groups.push(selections[i].data.id);
			}

			this.el.mask(t("Loading..."));
			GO.request({
				url: "groups/group/getRecipientsAsString",
				params: {
					groups: Ext.encode(user_groups)
				},
				success: function(options, response, result)
				{
					this.fireEvent('addrecipients', field, result.recipients);
					this.el.unmask();
				},
				scope:this
			});
		}else
		{
			var emails = [];

			for (var i = 0; i < selections.length; i++) {
				emails.push('"' + selections[i].data.name + '" <'
					+ selections[i].data.email + '>');
			}
					
			str=emails.join(', ');
			this.fireEvent('addrecipients', field, str);
		}
				
	}
});
