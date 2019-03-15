/* global go, Ext, GO */

go.modules.community.addressbook.MainPanel = Ext.extend(go.modules.ModulePanel, {

	layout: "responsive",

	// change responsive mode on 1000 pixels
	layoutConfig: {
		triggerWidth: 1000
	},

	addAddressBookId: 1,

	initComponent: function () {	
		
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

		this.contactDetail = new go.modules.community.addressbook.ContactDetail({
			region: "east",
			split: true,
			width: dp(500),
			tbar: [
				//add a back button for small screens
				{
					// this class will hide the button on large screens
					cls: 'go-narrow',
					iconCls: "ic-arrow-back",
					handler: function () {
						this.westPanel.show();
					},
					scope: this
				}]
		});

		this.westPanel = new Ext.Panel({
			region: "center",
			layout: "responsive",
			//stateId: "go-addressbook-west",
			split: true,
			narrowWidth: dp(400), //this will only work for panels inside another panel with layout=responsive. Not ideal but at the moment the only way I could make it work
			items: [
				this.grid, //first is default in narrow mode
				this.sidePanel
			]
		});

		this.items = [this.westPanel, this.contactDetail];

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);		


	},
	
	
	createAddressBookTree : function() {
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			enableDrop: true,
			ddGroup: "addressbook",
			ddAppendOnly: true,
			tbar: [{
					xtype: "tbtitle",
					text: t("Address books")
				}, '->', {
					iconCls: 'ic-add',
					tooltip: t("Add"),
					handler: function () {
						var dlg = new go.modules.community.addressbook.AddressBookDialog();
						dlg.show();
					}
				},
				//add back button for smaller screens
				{
					//this class will hide it on larger screens
					cls: 'go-narrow',
					iconCls: "ic-arrow-forward",
					tooltip: t("Contacts"),
					handler: function () {
						this.grid.show();
					},
					scope: this

				}]
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
		
		//init drag drop
		this.addressBookTree.on("nodedragover", this.onNodeDragOver, this);
		this.addressBookTree.on("beforenodedrop", this.onNodeDrop, this);
		
		return this.addressBookTree;
	},
	
	createGrid : function() {
		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			enableDragDrop: true, //for dragging contacts to address books or groups in the tree
			ddGroup: "addressbook",
			tbar: [
				{
					cls: 'go-narrow',
					iconCls: "ic-menu",
					handler: function () {
						this.sidePanel.show();
					},
					scope: this
				},
				'->',
				{
					xtype: 'tbsearch'
				},
				this.addButton = new Ext.Button({
					//disabled: true,
					iconCls: 'ic-add',
					tooltip: t('Add'),
					menu: [
						{
							iconCls: 'ic-account-box',
							text: t("Contact"),
							handler: function (btn) {
								var dlg = new go.modules.community.addressbook.ContactDialog();
								dlg.setValues({
									addressBookId: this.addAddressBookId,
									isOrganization: false
								});
								dlg.show();
							},
							scope: this
						},{
							iconCls: 'ic-business',
							text: t("Organization"),
							handler: function (btn) {
								var dlg = new go.modules.community.addressbook.ContactDialog();
								dlg.setValues({
									addressBookId: this.addAddressBookId,
									isOrganization: true
								});
								dlg.show();
							},
							scope: this
						}
					],					
					scope: this
				}),
				{
					iconCls: 'ic-more-vert',
					menu: [{
							iconCls: 'ic-cloud-upload',
							text: t("Import"),
							handler: function() {
								go.util.importFile(
												'Contact', 
												"text/vcard,application/json",
												{addressBookId: this.addAddressBookId});
							},
							scope: this
						}, {
							iconCls: 'ic-cloud-download',
							text: t("Export"),
							menu: [
								{
									text: 'vCard',
									iconCls: 'ic-contacts',
									handler: function() {
										go.util.exportToFile(
														'Contact', 
														Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0}),
														'text/vcard');									
									},
									scope: this
								},{
									text: 'CSV',
									iconCls: 'ic-description',
									handler: function() {
										go.util.exportToFile(
														'Contact', 
														Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0}),
														'text/csv');									
									},
									scope: this
								}
//								{
//									text: 'JSON',
//									handler: function() {
//										go.util.exportToFile(
//														'Contact', 
//														Ext.apply(this.grid.store.baseParams, this.grid.store.lastOptions.params, {limit: 0, start: 0}),
//														'application/json');									
//									},
//									scope: this
//								}
							]							
						},
						"-",
						{
							itemId: "delete",
							iconCls: 'ic-delete',
							text: t("Delete"),
							handler: function () {
								this.grid.deleteSelected();
							},
							scope: this
						}]
				}

			],
			listeners: {
				rowdblclick: this.onGridDblClick,
				
				keypress: this.onGridKeyPress,

				scope: this
			}
		});
		
		//Load contact when selecting it in the grid.
		this.grid.on('navigate', function (sm, rowIndex, record) {
			go.Router.goto("contact/" + record.id);
		}, this);
		
		return this.grid;
	},
	
	onGridDblClick : function (grid, rowIndex, e) {

		var record = grid.getStore().getAt(rowIndex);
		if (record.get('permissionLevel') < GO.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.addressbook.ContactDialog();
		dlg.load(record.id).show();
	},
	
	onGridKeyPress : function(e) {
		if(e.keyCode != e.ENTER) {
			return;
		}
		var record = this.grid.getSelectionModel().getSelected();
		if(!record) {
			return;
		}

		if (record.get('permissionLevel') < GO.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.addressbook.ContactDialog();
		dlg.load(record.id).show();

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
						var dlg = new go.modules.community.addressbook.FilterDialog();
						dlg.show();
					},
					scope: this
				}
			],
			items: [
				orgFilter,
				this.filterGrid = new go.modules.community.addressbook.FilterGrid({
					filterStore: this.grid.store
				})
			]
		});
		
		
	},

	setAddressBookId: function (addressBookId) {
		this.addButton.setDisabled(false);
		if (addressBookId) {
			this.addAddressBookId = addressBookId;
			
			this.grid.store.setFilter("addressbooks", {
				addressBookId: addressBookId
			});
			
		} else
		{
			this.grid.store.setFilter("addressbooks", null);
			
			var firstAbNode = this.addressBookTree.getRootNode().childNodes[1];
			if (firstAbNode) {
				this.addAddressBookId = firstAbNode.attributes.data.id;
			} else
			{
				this.addButton.setDisabled(true);
			}
		}
		
		this.grid.store.load();
	},

	setGroupId: function (groupId, addressBookId) {
	
		this.addAddressBookId = addressBookId;
		this.addButton.setDisabled(false);
		
		this.grid.store.setFilter('addressbooks', {
			addressBookId: addressBookId,
			groupId: groupId
		});
			
		this.grid.store.load();
	},

	onNodeDragOver: function (e) {
		if (e.target.id === "all") {
			return false;
		}

		if (e.target.attributes.data.permissionLevel < GO.permissionLevels.write) {
			return false;
		}

		return true;
	},

	onNodeDrop: function (e) {
		var updates = {};

		var removeFromGrid = false;

		//loop through dragged grid records
		e.source.dragData.selections.forEach(function (r) {
			var contact = {};

			if (e.target.attributes.entity.name === "AddressBook") {
				removeFromGrid = r.json.addressBookId !== e.target.attributes.data.id;
				contact.addressBookId = e.target.attributes.data.id;
				contact.groups = []; //clear groups when changing address book
			} else
			{
				removeFromGrid = r.json.addressBookId != e.target.attributes.data.addressBookId;
				//clear groups when changing address book				
				contact.groups = r.json.addressBookId == e.target.attributes.data.addressBookId ? GO.util.clone(r.json.groups) : [];
				contact.addressBookId = e.target.attributes.data.addressBookId;

				var groupId = e.target.attributes.data.id;
				if (contact.groups.column("groupId").indexOf(groupId) > -1) {
					return; //already in the groups
				}
				contact.groups.push({groupId: groupId});
			}

			updates[r.id] = contact;
		});

		//console.log(updates);

		if (removeFromGrid) {
			this.grid.store.remove(e.source.dragData.selections);
		}

		go.Stores.get("Contact").set({
			update: updates
		});

	}

});
