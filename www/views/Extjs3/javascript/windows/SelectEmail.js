/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SelectEmail.js 20201 2016-07-07 09:25:28Z mschering $
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
 * @extends Ext.Window A window to select a number of User-Office user Users.
 * 
 * @cfg {Function} handler A function called when the Add or Ok button is
 *      clicked. The grid will be passed as argument.
 * @cfg {Object} scope The scope of the handler
 * 
 * @constructor
 * @param {Object}
 *            config The config object
 */

GO.dialog.SelectEmail = function(config) {

	Ext.apply(this, config);

	var items = Array();
	this.usersStore = new GO.data.JsonStore({
		url : GO.url('core/users'),
		fields : ['id', 'username', 'name','email'],
		remoteSort : true
	});

	this.usersSearchField = new GO.form.SearchField({
		store : this.usersStore,
		width : 320
	});

	this.usersGrid = new GO.grid.GridPanel({
		id : 'select-users-grid',
		title : GO.lang.users,
		paging : true,
		border : false,
		store : this.usersStore,
		view : new Ext.grid.GridView({
			autoFill : true,
			forceFit : true
		}),
		columns : [{
			header : GO.lang['strName'],
			dataIndex : 'name',
			css : 'white-space:normal;',
			sortable : true
		}, {
			header : GO.lang['strEmail'],
			dataIndex : 'email',
			css : 'white-space:normal;',
			sortable : true
		}],
		sm : new Ext.grid.RowSelectionModel(),
		tbar : [GO.lang['strSearch'] + ': ', ' ', this.usersSearchField]
	});

	this.usersGrid.on('show', function() {
		this.usersStore.load();
	}, this);
			
	this.usersGrid.on('rowdblclick', function(){
		this.callHandler(true);
	}, this);
	
	/*
	 * this.usersGrid.on('afterRender', function(){
	 * if(this.usersGrid.isVisible()) { this.onShow(); } }, this);
	 */

	items.push(this.usersGrid);

	if (GO.addressbook) {
		this.contactsStore = new GO.data.JsonStore({
			url : GO.url("addressbook/contact/searchEmail"),
			id : 'email',
			fields : ['id', 'name',  'email', 'ab_name', 'company_name', "function","department"],
			remoteSort : true
		});

		this.contactsSearchField = new GO.form.SearchField({
			store : this.contactsStore,
			width : 320
		});

		this.contactsGrid = new GO.grid.GridPanel({
			id : 'select-contacts-grid',
			title : GO.addressbook.lang.contacts,
			paging : true,
			border : false,
			store : this.contactsStore,
			view : new Ext.grid.GridView({
				autoFill : true,
				forceFit : true
			}),
			columns : [{
				header : GO.lang['strName'],
				dataIndex : 'name',
				css : 'white-space:normal;',
				sortable : true
			}, {
				header : GO.lang['strEmail'],
				dataIndex : 'email',
				css : 'white-space:normal;',
				sortable : true
			}],
			sm : new Ext.grid.RowSelectionModel(),
			tbar : [GO.lang['strSearch'] + ': ', ' ',
			this.contactsSearchField]
		});

		this.contactsGrid.on('show', function() {
			this.contactsStore.load();
		}, this);
				
		this.contactsGrid.on('rowdblclick', function(){
			this.callHandler(true);
		}, this);

		

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

		this.companyGrid = new GO.grid.GridPanel({
			id : 'select-companies-grid',
			title : GO.addressbook.lang.companies,
			paging : true,
			border : false,
			store : this.companiesStore,
			view : new Ext.grid.GridView({
				autoFill : true,
				forceFit : true
			}),
			columns : [{
				header : GO.lang['strName'],
				dataIndex : 'name',
				css : 'white-space:normal;',
				sortable : true
			}, {
				header : GO.lang['strEmail'],
				dataIndex : 'email',
				css : 'white-space:normal;',
				sortable : true
			}],
			sm : new Ext.grid.RowSelectionModel(),
			tbar : [GO.lang['strSearch'] + ': ', ' ',
			this.companySearchField]
		});

		this.companyGrid.on('show', function() {
			this.companiesStore.load();
		}, this);
		
		this.companyGrid.on('rowdblclick', function(){
			this.callHandler(true);
		}, this);

		items.push(this.contactsGrid);
		items.push(this.companyGrid);

		if (GO.addressbook) {
			
			this.addresslistsStore = GO.addressbook.readableAddresslistsStore;
			
			this.addresslistsSearchField = new GO.form.SearchField({
				store : this.addresslistsStore,
				width : 320
			});
			
			this.mailingsGrid = new GO.grid.GridPanel({
				id : 'select-mailings-grid',
				title : GO.addressbook.lang.cmdPanelMailings,
				paging : true,
				border : false,
				store : this.addresslistsStore,
				view : new Ext.grid.GridView({
					autoFill : true,
					forceFit : true
				}),
				columns : [{
					header : GO.lang['strName'],
					dataIndex : 'name',
					css : 'white-space:normal;',
					sortable : true
				}],
				sm : new Ext.grid.RowSelectionModel(),
				tbar : [
					GO.lang['strSearch'] + ': ', ' ',
					this.addresslistsSearchField
				]
			});
			this.mailingsGrid.on('show', function() {
				if(!GO.addressbook.readableAddresslistsStore.loaded)
					GO.addressbook.readableAddresslistsStore.load();
			}, this);
			this.mailingsGrid.on('rowdblclick', function(){
				this.callHandler(true);
			}, this);

			items.push(this.mailingsGrid);
		}
	}

	this.userGroupsStore = new GO.data.JsonStore({
		url : GO.url("core/groups"),
		baseParams : {
			task : 'groups'
		},
		id : 'id',
		root : 'results',
		fields: ['id', 'name', 'user_id', 'user_name'],
		totalProperty : 'total',
		remoteSort : true
	});

		this.userGroupsSearchField = new GO.form.SearchField({
			store : this.userGroupsStore,
			width : 320
		});

	this.userGroupsGrid = new GO.grid.GridPanel({
		id : 'select-usergroups-grid',
		title : GO.lang.userGroups,
		paging : true,
		border : false,
		store : this.userGroupsStore,
		view : new Ext.grid.GridView({
			autoFill : true,
			forceFit : true
		}),
		columns : [{
			header : GO.lang['strName'],
			dataIndex : 'name',
			css : 'white-space:normal;',
			sortable : true
		}, {
			header : GO.lang['strOwner'],
			dataIndex : 'user_name',
			css : 'white-space:normal;',
			sortable : true
		}],
		sm : new Ext.grid.RowSelectionModel(),
		tbar : [
					GO.lang['strSearch'] + ': ', ' ',
					this.userGroupsSearchField
				]
	});

	this.userGroupsGrid.on('show', function() {
		this.userGroupsStore.load();
	}, this);

	this.userGroupsGrid.on('rowdblclick', function(){
		this.callHandler(true);
	}, this);

	items.push(this.userGroupsGrid);

	this.tabPanel = new Ext.TabPanel({
		activeTab : 0,
		items : items
	});

	Ext.Window.superclass.constructor.call(this, {
		layout : 'fit',
		modal : false,
		height : 400,
		width : 600,
		closeAction : 'hide',
		title : GO.lang['strSelectEmail'],
		items : this.tabPanel,
		buttons : [{
			text : GO.lang['cmdOk'],
			handler : function() {
				this.callHandler(true);
			},
			scope : this
		}, {
			text : GO.lang['cmdAdd'],
			handler : function() {
				this.callHandler(false);
			},
			scope : this
		}, {
			text : GO.lang['cmdClose'],
			handler : function() {
				this.hide();
			},
			scope : this
		}]
	});
};

Ext.extend(GO.dialog.SelectEmail, Ext.Window, {

	// private
	callHandler : function(hide) {

		if (this.handler) {
			if (!this.scope) {
				this.scope = this;
			}

			var activeGrid, type;

			switch (this.tabPanel.getLayout().activeItem.id) {
				case 'select-users-grid' :
					type='users';
					activeGrid = this.usersGrid;
					break;

				case 'select-contacts-grid' :
					type='contacts';
					activeGrid = this.contactsGrid;
					break;
				/*
				case 'select-addressbooks-grid' :
					type='addressbooks';
					activeGrid = this.addressbooksGrid;
					break;
*/
				case 'select-usergroups-grid' :
					type='usergroups';
					activeGrid = this.userGroupsGrid;
					break;

				case 'select-companies-grid' :
					type='companies';
					activeGrid = this.companyGrid;
					break;

				case 'select-mailings-grid' :
					type='mailings';
					activeGrid = this.mailingsGrid;
					break;
			}

			var handler = this.handler.createDelegate(this.scope, [activeGrid, type]);
			handler.call();

		}
		if (hide) {
			this.hide();
		}
	}

});
