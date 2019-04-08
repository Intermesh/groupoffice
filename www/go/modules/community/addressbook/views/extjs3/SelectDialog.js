/* global go, Ext, GO */

go.modules.community.addressbook.SelectDialog = Ext.extend(go.Window, {
	
	layout: "responsive",
	width: dp(1000),
	height: dp(800),
	modal: true,
	title: t("Select from address book"),
	selectMultiple: function(contactIds) {
		
	},
	selectSingle: function(name, email, id) { 
	
	},
	scope: null,
	initComponent : function() {
		
		if(!this.scope) {
			this.scope = this;
		}
		
		this.bbar = [
			'->',
			{
				text: t("Add all"),
				handler: this.selectAll,
				scope: this
			}
		];		
		
		this.createGrid();
		
		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			split: true,
			autoScroll: true,			
			items: [
				this.createAddressBookTree(),
				this.createFilterPanel()
			]
		});
		
		this.labels = t("emailTypes");
		
		this.items = [this.grid, this.sidePanel];
		
		go.modules.community.addressbook.SelectDialog.superclass.initComponent.call(this);
		
		
	},
	
	createGrid : function() {
		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
						this.addressBookTree.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				}
			],
			listeners: {
				rowclick: function (grid, rowIndex, e) {

					var record = grid.getStore().getAt(rowIndex), emails = record.get("emailAddresses");
					
					if(emails.length === 1) {
						this.selectSingle.call(this.scope, record.get("name"), emails[0].email, record.get("id"));
						this.close();
						return;
					}
					
					var me = this,  items = emails.map(function(a) {
						return {
							data: {
								name: record.get("name"),
								email: a.email,
								id: record.get("id")
							},
							text: "<div>" + a.email + "</div><small>" +  this.labels[a.type] + "</div>",
							handler: function() {
								me.selectSingle.call(me.scope, this.data.name, this.data.email, this.data.id);
								me.close();
							}
						};
					}, this);
					
					var m = new Ext.menu.Menu({
						cls: "x-menu-no-icons",
						items: items
					});
					
					m.showAt(e.getXY());
					
				},

				scope: this
			}
		});
		
		this.grid.store.setFilter("required", {			
			hasEmailAddresses: true
		});
		
		return this.grid;
	},
	
	createAddressBookTree : function() {
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			width: dp(300),
			region: "west",
			split: true,
			readOnly: true,
			scope: this
		});	
		
		//because the root node is not visible it will auto expand on render.
		this.addressBookTree.getRootNode().on('expand', function (node) {
			//when expand is done we'll select the first node. This will trigger a selection change. which will load the grid below.
			this.addressBookTree.getSelectionModel().select(node.firstChild);
		}, this);

		//load the grid on selection change.
		this.addressBookTree.getSelectionModel().on('selectionchange', function (sm, node) {
			
			if (!node) {
				return;
			}

			if (node.id === "all") {
				this.setAddressBookId(null);
			} else if (node.attributes.entity.name === "AddressBook") {
				this.setAddressBookId(node.attributes.data.id);
			} else
			{
				this.setGroupId(node.attributes.data.id, node.attributes.data.addressBookId);
			}
		}, this);
		
		return this.addressBookTree;
	},
	
	createFilterPanel: function () {
		var orgFilter = new go.NavMenu({			
			store: new Ext.data.ArrayStore({
				fields: ['name', 'icon', 'inputValue'], //icon and iconCls are supported.
				data: [					
					[t("Organization"), 'business', true],
					[t("Contact"), 'person', false],
					['-']
				]
			}),
			simpleSelect: true,
			multiSelect : true,
			listeners: {
				selectionchange: function (view, nodes) {
					if(!nodes.length || nodes.length == 2) {
						this.grid.store.setFilter("org", null);
					} else
					{
						var record = view.store.getAt(nodes[0].viewIndex);
						this.grid.store.setFilter("org", {isOrganization: record.data.inputValue});
					}					
					this.grid.store.load();
				},
				scope: this
			}
		});		
		
		
		return new Ext.Panel({
			
			tbar: [
				{
					xtype: 'tbtitle',
					text: t("Filters")
				},
				'->',
				{
					xtype: "button",
					iconCls: "ic-add",
					handler: function() {
						var dlg = new go.filter.FilterDialog({
							entity: "Contact"
						});
						dlg.show();
					},
					scope: this
				}
			],
			items: [
				orgFilter,
				this.filterGrid = new go.filter.FilterGrid({
					filterStore: this.grid.store,
					entity: "Contact"
				})
			]
		});
	},
	
	setAddressBookId: function (addressBookId) {		
		this.grid.store.setFilter("addressbooks", addressBookId ? {
			addressBookId: addressBookId
		} : null);
		
		this.grid.store.load();
	},

	setGroupId: function (groupId, addressBookId) {
		this.grid.store.setFilter("addressbooks", {
			groupId: groupId
		});
		
		this.grid.store.load();
	},
	
	select : function(contactIds) {
		this.handler.call(this.scope, contactIds);
		this.close();
	},
	
	selectAll : function() {
		var s = go.Stores.get("Contact");
		this.getEl().mask(t("Loading..."));
		s.query({
			filter: this.grid.store.getFilter()
		}, function(response) {			
			this.selectMultiple.call(this.scope, response.ids);
			this.getEl().unmask();
			this.close();
		}, this);
	}
	
});
