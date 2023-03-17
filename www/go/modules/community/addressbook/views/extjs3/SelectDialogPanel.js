/* global go, Ext, GO */

go.modules.community.addressbook.SelectDialogPanel = Ext.extend(Ext.Panel, {
	
	layout: "border",
	mode: "email", // or "id" in the future "phone" or "address"	
	entityName:  "Contact",
	title: t("Address Book"),
	query: "",
	selectSingle: false,
	initComponent : function() {
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


		this.searchField = new go.SearchField({
			anchor: "100%",
			handler: function(field, v){
				this.search(v);
			},
			emptyText: null,
			scope: this,
			value: this.query
		});

		const search = new Ext.Panel({
			layout: "form",
			region: "north",
			autoHeight: true,
			items: [{
					xtype: "fieldset",
					items: [this.searchField]
				}]
		});

		this.items = [search, {xtype:'panel',layout:'responsive',region:'center', items:[this.grid,  this.sidePanel]}];
		
		this.grid.getSelectionModel().singleSelect = this.singleSelect;		
		
		go.modules.community.addressbook.SelectDialogPanel.superclass.initComponent.call(this);

		this.addressBookTree.getLoader().on('load', (loader, node, response) => {
			this.addressBookTree.getBottomToolbar().setVisible(response.queryResponse.hasMore);
			this.loadMoreButton.setVisible(response.queryResponse.hasMore);
		});

		this.on("show", function() {
			this.searchField.focus();			
		}, this);		
		
	},

	search : function(v) {
		this.grid.store.setFilter("search", {text: v});
		this.grid.store.load();
		this.searchField.focus();
	},
	
	createGrid : function() {

		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',

			listeners: {
				rowclick: function (grid, rowIndex, e) {
					if(e.ctrlKey || e.shiftKey) {
						return;
					}
					const record = grid.getStore().getAt(rowIndex);
					if(this.mode === 'email') {
						this.selectEmail(record, e);
					}
				},
				scope: this
			}
		});

		this.grid.getView().totalDisplay = false;

		if(this.mode == 'email') {
			this.grid.store.setFilter("required", {
				hasEmailAddresses: true
			});
		}
		
		return this.grid;
	},
	
	createAddressBookTree : function() {
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			region: "west",
			split: true,
			readOnly: true,
			scope: this,
			tbar: [{
				xtype: "tbtitle",
				text: t("Address books")
			}, '->', {
				xtype: "tbsearch"
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

			}],
			bbar: new Ext.Toolbar({
				cls: 'go-bbar-load-more',
				items:[
					'',
					this.loadMoreButton = new Ext.Button({
						hidden: true,
						text: t("Show more..."),
						handler: () => {


							const loader = this.addressBookTree.getLoader();

							loader.clearOnLoad = false;
							loader.position += loader.pageSize;

							loader.load(this.addressBookTree.getRootNode(), (node) => {
								loader.clearOnLoad = true;
							});
						}
					})
				]
			})
		});

		this.selectedAbs = {};
		//because the root node is not visible it will auto expand on render.
		this.addressBookTree.getRootNode().on('expand', function (node) {
			//when expand is done we'll select the first node. This will trigger a selection change. which will load the grid below.
			this.addressBookTree.getSelectionModel().select(node.firstChild);
		}, this);

		this.addressBookTree.on('checkchange', function (node, checked) {
			this.grid.store.setFilter('groups', null);
			if (node) {
				this.doNotSelected = true;
				this.addressBookTree.getSelectionModel().select(node);
			}
			this.selectAddressbook(node.attributes.data.id, checked);
		},this );

		this.addressBookTree.getSelectionModel().on('selectionchange', function (sm, node) {
			if (!node || this.doNotSelected) {
				this.doNotSelected = false;
				return;
			}
			if (node.attributes.entity.name === "AddressBook") {
				this.grid.store.setFilter('groups', null);
				this.deselectNodes();
				node.ui.checkbox.checked = true;
				this.selectAddressbook(node.attributes.data.id, true);
			} else { // treenode is group

				this.addButton.setDisabled(false);

				this.deselectNodes();
				node.parentNode.ui.checkbox.checked = true;
				this.selectAddressbook(node.parentNode.attributes.data.id, true);

				this.grid.store.setFilter('groups', {groupId: node.attributes.data.id, addressbookId: node.parentNode.attributes.data.id} );
				this.grid.store.load()
			}

		}, this);

		//load the grid on selection change.
		// this.addressBookTree.getSelectionModel().on('selectionchange', function (sm, node) {
		//
		// 	if (!node) {
		// 		return;
		// 	}
		//
		// 	this.grid.store.setFilter("starred", null);
		//
		// 	if (node.id === "all") {
		// 		this.setAddressBookId(null);
		// 	} else if (node.id === "starred") {
		//
		// 		this.grid.store.setFilter("starred", {starred: true});
		// 		this.setAddressBookId(null);
		// 	} else if (node.attributes.entity.name === "AddressBook") {
		// 		this.setAddressBookId(node.attributes.data.id);
		// 	} else
		// 	{
		// 		this.setGroupId(node.attributes.data.id, node.attributes.data.addressBookId);
		// 	}
		// }, this);
		
		return this.addressBookTree;
	},

	deselectNodes() {
		this.selectedAbs = {};
		this.addressBookTree.getRootNode().childNodes.forEach(node => {node.ui.checkbox.checked = false});
	},

	selectAddressbook(id, enabled) {
		this.selectedAbs[id] = enabled;

		const abIds = Object.keys(this.selectedAbs)
			.filter(k=> this.selectedAbs[k])
			.map(Number)
		this.setAddressBookId(abIds);
	},
	
	createFilterPanel: function () {
		const orgFilter = new go.NavMenu({
			store: new Ext.data.ArrayStore({
				fields: ['name', 'icon', 'inputValue'], //icon and iconCls are supported.
				data: [					
					[t("Organization"), 'business', true],
					[t("Contact"), 'person', false]					
				]
			}),
			simpleSelect: true,
			multiSelect : true,
			listeners: {
				selectionchange: function (view, nodes) {
					if(!nodes.length || nodes.length == 2) {
						this.grid.store.setFilter("org", null);
					} else {
						const record = view.store.getAt(nodes[0].viewIndex);
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
					xtype: 'filteraddbutton',
					entity: 'Contact'
				}
			],
			items: [
				orgFilter,
				
				{xtype: "box", autoEl: "hr"},

				{
					xtype: 'filtergrid',
					filterStore: this.grid.store,
					entity: "Contact"
				},
				{
					xtype: 'variablefilterpanel',
					filterStore: this.grid.store,
					entity: "Contact"
				}
			]
		});
	},
	
	setAddressBookId: function (addressBookId) {		
		this.grid.store.setFilter("addressbooks", addressBookId ? {
			addressBookId: addressBookId
		} : null);
		

		this.search(this.searchField.getValue());				
	},

	setGroupId: function (groupId, addressBookId) {
		this.grid.store.setFilter("addressbooks", {
			groupId: groupId
		});
		
		this.grid.store.load();
	},	

	addAll : function() {
		const me = this;
		const promise = new Promise(function(resolve, reject) {
		
			const s = go.Db.store("Contact");
			me.getEl().mask(t("Loading..."));
			s.query({
				filter: me.grid.store.baseParams.filter
			}, function(response) {			
				me.getEl().unmask();
				Ext.MessageBox.confirm(t("Confirm"), t("Are you sure you want to select all {count} results?").replace('{count}', response.ids.length), function(btn) {
					if(btn != 'yes') {
						reject();
					}
					resolve(response.ids);
				}, me);
				
			}, me);
		});

		return promise;
	},

	addSelection : function() {
		const records = this.grid.getSelectionModel().getSelections();
		return Promise.resolve(records.column('id'));
	},
	
	
	selectEmail : function(record, e) {
		const emails = record.get("emailAddresses");
				
		const me = this,  items = emails.map(function(a) {
			return {
				data: {
					name: record.get("name"),
					email: a.email,
					id: record.get("id")
				},
				text: "<div>" + a.email + "</div><small>" +  this.labels[a.type] + "</div>",
				handler: function() {
					me.fireEvent('selectsingle', me, this.data.name, this.data.email, this.data.id);
				}
			};
		}, this);

		const m = new Ext.menu.Menu({
			cls: "x-menu-no-icons",
			items: items
		});

		m.showAt(e.getXY());
	}
	
});
