/* global go, Ext, GO */

go.modules.community.addressbook.MainPanel = Ext.extend(go.modules.ModulePanel, {

	layout: "responsive",

	// change responsive mode on 1000 pixels
	layoutConfig: {
		triggerWidth: 1000
	},

	addAddressBookId: undefined,

	initComponent: function () {	
		
		this.createGrid();

		this.sidePanel = new Ext.Panel({
			width: dp(300),
			cls: 'go-sidenav',
			region: "west",
			split: true,
			autoScroll: true,
			layout: "anchor",
			defaultAnchor: '100%',
			items: [
				this.navMenu = new go.NavMenu({
					region:'north',
					listeners: {
						"afterrender": me => {
							let statusFilter = Ext.state.Manager.get("addressbook-status-filter");
							if(!statusFilter) {
								statusFilter = 'allcontacts';
							}
							let index = me.store.find('inputValue', statusFilter);
							if(index == -1) {
								index = 0;
							}
							me.selectRange(index,index);
						},
						'selectionchange' : (view, nodes) => {
							if(!nodes.length) return;
							const rec = view.store.getAt(nodes[0].viewIndex);
							this.grid.store.setFilter("org", null);
							this.grid.store.setFilter("starred", null);
							switch(rec.data.inputValue){
								case 'organization':
								case 'contacts':
									this.grid.store.setFilter("org", {isOrganization: rec.data.inputValue == 'organization'});
									break;
								case 'starred':
									this.grid.store.setFilter("starred", {starred: true});
									break;
							}
							this.grid.store.load();
						}
					},
					store: new Ext.data.ArrayStore({
						fields: ['name', 'icon', 'iconCls', 'inputValue'],
						data: [
							[t("All contacts", "addressbook", "community"), 'select_all', 'blue', 'all'],
							[t("Starred", "addressbook", "community"), 'star', 'orange', 'starred'],
							[t("Organization"), 'business', 'green', 'organization'],
							[t("Contacts"), 'person', 'blue','contacts']
						]
					})
				}),
				this.createAddressBookTree(),
				{xtype:'filterpanel', store: this.grid.store, entity: 'Contact'}
			]
		});

		this.contactDetail = new go.modules.community.addressbook.ContactDetail({
			region: "east",
			split: true,

			tbar: [
				//add a back button for small screens
				{
					// this class will hide the button on large screens
					cls: 'go-narrow',
					iconCls: "ic-arrow-back",
					handler: function () {
						go.Router.goto("addressbook");
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

		// route to main grid when details resets / is deleted for mobile view
		this.contactDetail.on("reset", () => {
			go.Router.goto("addressbook");
		})

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);		


	},
	
	
	createAddressBookTree : function() {
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			region:  "north",
			stateId:'ab-tree',
			split: true,
			enableDrop: true,
			readOnly: false, //!go.Modules.get("community", 'addressbook').userRights.mayChangeAddressbooks,
			ddGroup: "addressbook",
			ddAppendOnly: true,
			tbar: [{
					xtype: "tbtitle",
					text: t("Address books")
				}, '->',{
					xtype: "tbsearch"
				}, {
					hidden: !go.Modules.get("community", 'addressbook').userRights.mayChangeAddressbooks,
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

		this.addressBookTree.getLoader().on('load', (loader, node, response) => {
			this.addressBookTree.getBottomToolbar().setVisible(response.queryResponse.hasMore);
			this.loadMoreButton.setVisible(response.queryResponse.hasMore);
			this.sidePanel.doLayout();
		});
		
		
		//because the root node is not visible it will auto expand on render. This depends on the user address book settings
		this.addressBookTree.getRootNode().on('expand', function (node) {
			var abSettings = go.User.addressBookSettings, abNode = null;

			this.addAddressBookId = abSettings.defaultAddressBookId;

			if (abSettings.startIn == "allcontacts") {
				//when expand is done we'll select the first node. This will trigger a selection change. which will load the grid below.
				//abNode = node.firstChild;
				// when we select nothong the grid will load without filter
			} else if (abSettings.startIn == "starred") {
				//abNode = node.findChild('id', 'starred');
				this.navMenu.select(1); // = starred item
			} else if (abSettings.startIn == "remember" && abSettings.lastAddressBookId > 0) {
				abNode = node.findChild('id', 'AddressBook-' + abSettings.lastAddressBookId);
			} else {
				abNode = node.findChild('id', 'AddressBook-' + abSettings.defaultAddressBookId);
			}
			if (abNode) {
				this.addressBookTree.getSelectionModel().select(abNode);
			}

		}, this);

		//load the grid on selection change.
		this.selectedAbs = {};
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
		
		//init drag drop
		this.addressBookTree.on("nodedragover", this.onNodeDragOver, this);
		this.addressBookTree.on("beforenodedrop", this.onNodeDrop, this);
		
		return this.addressBookTree;
	},

	selectedAbs: {},

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
	
	createGrid : function() {
		this.grid = new go.modules.community.addressbook.ContactGrid({
			region: 'center',
			enableDragDrop: true, //for dragging contacts to address books or groups in the tree
			ddGroup: "addressbook",
			multiSelectToolbarItems: [
				{
					iconCls: "ic-merge-type",
					tooltip: t("Merge"),
					handler: function() {
						const ids = this.grid.getSelectionModel().getSelections().column('id');
						if(ids.length < 2) {
							Ext.MessageBox.alert(t("Error"), t("Please select at least two items"));
						} else
						{
							Ext.MessageBox.confirm(t("Merge"), t("The selected items will be merged into one. The item you selected first will be used primarily. Are you sure?"), async function(btn) {

								if(btn != "yes") {
									return;
								}

								try {
									Ext.getBody().mask(t("Saving..."));
									const result = await go.Db.store("Contact").merge(ids);
									await go.Db.store("Contact").getUpdates();

									setTimeout(() => {
										const dlg = new go.modules.community.addressbook.ContactDialog();
										dlg.load(result.id);
										dlg.show();
									})
								} catch(e) {
									Ext.MessageBox.alert(t("Error"), e.message);
								} finally {
									Ext.getBody().unmask();
								}
							}, this);
						}
					},
					scope: this
				},
				{
					hidden: go.customfields.CustomFields.getFieldSets('Contact').length == 0,
					iconCls: 'ic-edit',
					tooltip: t("Batch edit"),
					handler: function() {
						var dlg = new go.form.BatchEditDialog({
							entityStore: "Contact"
						});
						dlg.setIds(this.grid.getSelectionModel().getSelections().column('id')).show();
					},
					scope: this
				}
			],
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
					iconCls: 'ic-add',
					cls: "primary",
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
					menu: [this.importButton = new Ext.menu.Item({
							iconCls: 'ic-cloud-upload',
							text: t("Import"),
							handler: function() {
								go.util.importFile(
												'Contact', 
												".csv, .vcf, text/vcard, .json, .xlsx",
												{addressBookId: this.addAddressBookId},
												{
													// These fields can be selected to update contacts if ID or e-mail matches
													lookupFields: {'id' : "ID", 'email': 'E-mail'},

													// This hash map is used to aid in auto selecting the right mappings. Key is possible header in CSV and value is property name in Group-Office
													aliases : {
														"Given name": "firstName",
														"First name": "firstName",

														"Middle name": "middleName",

														"Family Name": "lastName",
														"Last Name": "lastName",

														"Job Title": "jobTitle",
														"Suffix": "suffixes",
														"Web page" : {field: "urls[].url", fixed: {"type": "homepage"}},
														"Birthday" : {field: "dates[].date", fixed: {"type": "birthday"}},
														"Anniversary" : {field: "dates[].date", fixed: {"type": "anniversary"}},

														"E-mail 1 - Value": {field: "emailAddresses[].email", related: {"type": "E-mail 1 - Type"}},
														"email": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail 2 Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail 3 Address": {field: "emailAddresses[].email", fixed: {"type": "work"}},
														"E-mail": {field: "emailAddresses[].email", fixed: {"type": "work"}},

														"Primary Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
														"Home Phone": {field: "phoneNumbers[].number", fixed: {"type": "home"}},
														"Home Phone 2": {field: "phoneNumbers[].number", fixed: {"type": "home"}},

														"Business Phone": {field: "phoneNumbers[].number", fixed: {"type": "work"}},
														"Business Phone 2": {field: "phoneNumbers[].number", fixed: {"type": "work"}},

														"Mobile Phone": {field: "phoneNumbers[].number", fixed: {"type": "mobile"}},
														"Pager": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
														"Home Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

														"Other Phone": {field: "phoneNumbers[].number", fixed: {"type": "other"}},
														"Other Fax": {field: "phoneNumbers[].number", fixed: {"type": "fax"}},

														"Home Street": {
															field: "addresses[].street",
															fixed: {type: "home"},
															related: {
																city: "Home City",
																state: "Home State",
																zipCode: "Home Postal Code",
																country: "Home Country"
															}
														},
														"Business Street": {
															field: "addresses[].street",
															fixed: {type: "work"},
								  							related: {
																city: "Business City",
																state: "Business State",
																zipCode: "Business Postal Code",
																country: "Business Country"

															}
														},
														"Other Street": {
															field: "addresses[].street",
															fixed: {type: "other"},
															related: {
																city: "Other City",
																state: "Other State",
																zipCode: "Other Postal Code",
																country: "Other Country"

															}
														},

														"Company" : "organizations"
													},

													// Fields with labels and possible subproperties.
													// For example e-mail and type of an array of e-mail addresses should be grouped together.
													fields: {
														prefixes: {label: t("Prefixes")},
														initials: {label: t("Initials")},
														salutation: {label: t("Salutation")},
														color: {label: t("Color")},
														firstName: {label: t("First name")},
														middleName: {label: t("Middle name")},
														lastName: {label: t("Last name")},
														name: {label: t("Name")},
														suffixes: {label: t("Suffixes")},
														gender: {label: t("Gender")},
														notes: {label: t("Notes")},
														isOrganization: {label: t("Is organization")},
														IBAN: {label: t("IBAN")},
														registrationNumber: {label: t("Registration number")},
														vatNo: {label: t("VAT number")},
														vatReverseCharge: {label: t("Reverse charge VAT")},
														debtorNumber: {label: t("Debtor number")},
														photoBlobId: {label: t("Photo blob ID")},
														language: {label: t("Language")},
														jobTitle: {label: t("Job title")},
														uid: {label: t("UUID")},
														//starred: {label: t("Starred")},

														"emailAddresses": {
															label: t("E-mail addresses"),
															properties: {
																"email": {label: "E-mail"},
																"type": {label: t("Type")}
															}
														},

														"dates": {
															label: t("Dates"),
															properties: {
																"date": {label: "Date"},
																"type": {label: t("Type")}
															}
														},

														"phonenumbers": {
															label: t("Phone numbers"),
															properties: {
																"number": {label: "Number"},
																"type": {label: t("Type")}
															}
														},

														"urls": {
															label: t("URL's"),
															properties: {
																"url": {label: "URL"},
																"type": {label: t("Type")}
															}
														},

														"addresses": {
															label: t("Addresses"),
															properties: {
																"type": {label: t("Type")},
																"street": {label: t("Street")},
																"street 2": {label: t("Street 2")},
																"zipCode": {label: t("ZIP code")},
																"city": {label: t("City")},
																"state": {label: t("state")},
																"country": {label: t("Country")},
																"countryCode": {label: t("Country code")},
																"latitude": {label: t("Latitude")},
																"longitude": {label: t("Longitude")}
															}
														}

													}
												});
							},
							scope: this
						}), this.exportButton = new Ext.menu.Item({
							iconCls: 'ic-cloud-download',
							text: t("Export"),
							menu: [
								{
									text: 'vCard (Virtual Contact File)',
									iconCls: 'filetype filetype-vcf',
									handler: function() {
										go.util.exportToFile(
														'Contact',
														Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
														'vcf');
									},
									scope: this
								}, {
									text: 'Microsoft Excel',
									iconCls: 'filetype filetype-xls',
									handler: function() {
										go.util.exportToFile(
											'Contact',
											Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
											'xlsx');
									},
									scope: this
								},{
									text: 'Comma Separated Values',
									iconCls: 'filetype filetype-csv',
									handler: function() {
										go.util.exportToFile(
														'Contact',
														Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
														'csv');
									},
									scope: this
								},{
									text: t("Web page") + " (HTML)",
									iconCls: 'filetype filetype-html',
									handler: function() {
										go.util.exportToFile(
											'Contact',
											Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
											'html');
									},
									scope: this
								},
								{
									iconCls: 'filetype filetype-json',
									text: 'JSON',
									handler: function() {
										go.util.exportToFile(
											'Contact',
											Ext.apply(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, start: 0}),
											'json');
									},
									scope: this
								},
								'-',
								{
									iconCls: 'ic-print',
									text: t("Labels"),
									scope: this,
									handler: function() {
										var dlg = new go.modules.community.addressbook.LabelsDialog({
											queryParams: Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {
												limit: 0,
												position: 0
											})
										});
										dlg.show();

									}
								}

							]							
						}),
						"-",

						{
							iconCls: 'ic-merge-type',
							text: t("Look for duplicates"),
							handler: function() {
								var win = new go.modules.community.addressbook.DuplicateDialog();
								win.show();
							}
						}



					]
				}

			],
			listeners: {
				rowdblclick: this.onGridDblClick,
				
				keypress: this.onGridKeyPress,
				'rowcontextmenu': function (grid, rowIndex, e) {
					e.stopEvent();
					var sm = this.grid.getSelectionModel();
					if (sm.isSelected(rowIndex) !== true) {
						sm.clearSelections();
						sm.selectRow(rowIndex);
					}

					this.showContextMenu(e);
				},
				scope: this
			}
		});
		
		//Load contact when selecting it in the grid.
		this.grid.on('navigate', function (sm, rowIndex, record) {
			go.Router.goto("contact/" + record.id);
		}, this);
		
		return this.grid;
	},

	showContextMenu(e) {
		const m = new go.modules.community.addressbook.ContactContextMenu();
		let records = this.grid.getSelectionModel().getSelections();

		m.setRecords(records);
		m.showAt(e.getXY());
	},
	
	onGridDblClick : function (grid, rowIndex, e) {

		var record = grid.getStore().getAt(rowIndex);
		if (record.get('permissionLevel') < go.permissionLevels.write) {
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

		if (record.get('permissionLevel') < go.permissionLevels.write) {
			return;
		}

		var dlg = new go.modules.community.addressbook.ContactDialog();
		dlg.load(record.id).show();

	},

	setAddressBookId: function (addressBookIds) {
		this.addButton.setDisabled(false);
		const mayExportContacts = go.Modules.get("community", 'addressbook').userRights.mayExportContacts;

		this.importButton.setDisabled(false); // Only export is restricted to the restrictExportToAdmins setting!
		this.exportButton.setDisabled(!mayExportContacts);
		if (addressBookIds.length) {
			this.addAddressBookId = addressBookIds[0];
			this.addressBookTree.rememberLastAddressboek(addressBookIds[0]);
			
			this.grid.store.setFilter("addressbooks", {
				addressBookId: addressBookIds
			});
			
		} else {
			this.grid.store.setFilter("addressbooks", null);
			
			let firstAbNode = this.addressBookTree.getRootNode().childNodes[0];
			if (firstAbNode) {
				this.addAddressBookId = go.User.addressBookSettings && go.User.addressBookSettings.defaultAddressBookId ? go.User.addressBookSettings.defaultAddressBookId : firstAbNode.attributes.data.id;
			} else {
				this.addButton.setDisabled(true);
			}
		}
		const me = this;
		this.grid.store.load().then(function (result) {
			if (addressBookIds[0]) {
				go.Db.store('AddressBook').single(addressBookIds[0]).then(function (ab) {
					if (ab.permissionLevel < go.permissionLevels.create) {
						me.addButton.setDisabled(true);
						me.importButton.setDisabled(true);
						me.exportButton.setDisabled(true);
					}
				});
			}
		});
	},

	onNodeDragOver: function (e) {
		if (e.target.id === "all") {
			return false;
		}

		if (e.target.attributes.data.permissionLevel < go.permissionLevels.write) {
			return false;
		}

		return true;
	},

	onNodeDrop: function (e) {
		var updates = {};

		var removeFromGrid = false, me = this;

		//loop through dragged grid records

		function cb() {
			go.Db.store("Contact").get(e.source.dragData.selections.map(function (r) {
				return r.id
			})).then(function (result) {
				result.entities.forEach(function (c) {
					var contact = {};

					if (e.target.attributes.entity.name === "AddressBook") {
						removeFromGrid = c.addressBookId !== e.target.attributes.data.id;
						contact.addressBookId = e.target.attributes.data.id;
						contact.groups = []; //clear groups when changing address book
					} else {
						removeFromGrid = c.addressBookId != e.target.attributes.data.addressBookId;
						//clear groups when changing address book
						contact.groups = c.addressBookId == e.target.attributes.data.addressBookId ? go.util.clone(c.groups) : [];
						contact.addressBookId = e.target.attributes.data.addressBookId;

						var groupId = e.target.attributes.data.id;
						if (contact.groups.indexOf(groupId) > -1) {
							return; //already in the groups
						}
						contact.groups.push(groupId);
					}

					updates[c.id] = contact;
				});

				//console.log(updates);

				if (removeFromGrid) {
					me.grid.store.remove(e.source.dragData.selections);
				}

				go.Db.store("Contact").set({
					update: updates
				}).then(function (response) {
					if (!go.util.empty(response.notUpdated)) {
						Ext.MessageBox.alert(t("Error"), t("Failed to add contacts to the group"));
					}
				})
			});
		}

		go.User.confirmOnMove ?
			Ext.Msg.confirm(t('Confirm'), t('Are you sure you want to move the item(s)?'), function(btn) { if(btn == 'yes') cb.call(this)}, this) :
			cb.call(this);



	}

});
