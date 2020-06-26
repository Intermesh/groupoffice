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
			// autoScroll: true,
			layout: "border",
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

		go.modules.community.addressbook.MainPanel.superclass.initComponent.call(this);		


	},
	
	
	createAddressBookTree : function() {
		this.addressBookTree = new go.modules.community.addressbook.AddressBookTree({
			region:  "north",
			split: true,
			containerScroll: true,
			autoScroll: true,
			height: dp(300),
			minHeight: dp(200),
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
			multiSelectToolbarItems: [
				{
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
					//disabled: true,
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
					menu: [{
							iconCls: 'ic-cloud-upload',
							text: t("Import"),
							handler: function() {
								go.util.importFile(
												'Contact', 
												".csv, .vcf, text/vcard",
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
																street2: "Home Street 2",
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
																street2: "Business Street 2",
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
																street2: "Other Street 2",
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
														starred: {label: t("Starred")},

														"emailAddresses": {
															label: t("E-mail address"),
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
														Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
														'vcf');
									},
									scope: this
								},{
									text: 'CSV',
									iconCls: 'ic-description',
									handler: function() {
										go.util.exportToFile(
														'Contact',
														Object.assign(go.util.clone(this.grid.store.baseParams), this.grid.store.lastOptions.params, {limit: 0, position: 0}),
														'csv');
									},
									scope: this
								}, '-',
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
							iconCls: 'ic-content-copy',
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
	
	createFilterPanel: function () {
		var orgFilter = new go.NavMenu({			
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
			region: "center",
			minHeight: dp(200),
			autoScroll: true,
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
				this.addAddressBookId = go.User.addressBookSettings && go.User.addressBookSettings.defaultAddressBookId ? go.User.addressBookSettings.defaultAddressBookId : firstAbNode.attributes.data.id;
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

		if (e.target.attributes.data.permissionLevel < go.permissionLevels.write) {
			return false;
		}

		return true;
	},

	onNodeDrop: function (e) {
		var updates = {};

		var removeFromGrid = false, me = this;

		//loop through dragged grid records

		go.Db.store("Contact").get(e.source.dragData.selections.map(function(r){return r.id})).then(function(result) {
			result.entities.forEach(function (c) {
				var contact = {};

				if (e.target.attributes.entity.name === "AddressBook") {
					removeFromGrid = c.addressBookId !== e.target.attributes.data.id;
					contact.addressBookId = e.target.attributes.data.id;
					contact.groups = []; //clear groups when changing address book
				} else
				{
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
			}).then(function(response) {
				if(!go.util.empty(response.notUpdated)) {
					Ext.MessageBox.alert(t("Error"), t("Failed to add contacts to the group"));
				}
			})
		});



	}

});
