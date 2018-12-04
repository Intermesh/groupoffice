/* global go, Ext */

go.modules.community.addressbook.AddressBookTree = Ext.extend(Ext.tree.TreePanel, {
	
	rootVisible: false,

	currentAddressBookId: null,
	
	/**
	 * If given then the tree can be used to select contacts in bulk by selecting
	 * an address book or group. selectHandler will be called with the entities
	 * as array.
	 */
	selectHandler: null,
	scope: null,
	
	initComponent: function () {

		if(this.selectHandler === null) {
			this.loader = new go.modules.community.addressbook.TreeLoader();
		} else
		{
			this.loader = new go.modules.community.addressbook.TreeLoader({
				secondaryTextTpl: '<button class="icon">add</button>'
			});
		}
		
		this.root = {
			nodeType: 'groupoffice',
			draggable: false,
			id: null,
			uiProvider: Ext.tree.RootTreeNodeUI // needed to make "rootVisible" work
		};
		
		go.modules.community.addressbook.AddressBookTree.superclass.initComponent.call(this);


//		this.getSelectionModel().on("selectionchange", this.onSelectionChange, this);

		this.getRootNode().on("load", function () {
			go.Stores.get("AddressBook").on('changes', this.onAddressBookChanges, this);
			go.Stores.get("AddressBookGroup").on('changes', this.onGroupChanges, this);

			this.on("destroy", function () {
				go.Stores.get("Addressbook").un('changes', this.onAddressBookChanges, this);
				go.Stores.get("AddressBookGroup").un('changes', this.onGroupChanges, this);
			});
		}, this, {single: true});


		this.on("click", function (node, e) {

			if (e.target.tagName === "BUTTON") {

				if(!this.selectHandler) {
					if(node.attributes.entity.name === "AddressBook") {
						this.showAddressBookMoreMenu(node, e);
					} else
					{
						this.showGroupMoreMenu(node, e);
					}
				} else
				{
					if(node.attributes.entity.name === "AddressBook") {
						this.selectAddressBook(node.attributes.data.id);
					} else
					{
						this.selectGroup(node.attributes.data.id);
					}
				}
			}
		}, this);
	},

	findAddressbookNode: function (id) {
		var rootNode = this.getRootNode(), found = false;

		rootNode.findChildBy(function (node) {
			if (node.attributes.entity && node.attributes.entity.id === id) {
				found = node;
				return false;
			}
		});

		return found;
	},

	onAddressBookChanges: function (entityStore, added, changed, destroyed) {

		//reload if added address book is not present in tree yet.
		var me = this, reload = false, id;
		for (id in added) {
			if (!me.findAddressbookNode(id)) {
				reload = true;
				return false;
			}
		}
		;

		if (reload) {
			me.getRootNode().reload();
			return;
		}


		for (id in changed) {

			nodeId = "AddressBook-" + id,
							node = me.getNodeById(nodeId);

			if (node) {
				node.attributes.data = changed[id];

				if (changed[id].name) {
					node.setText(changed[id].name);
				}

				if (changed[id].groups) {
					delete node.attributes.children;
					node.reload();
				}
			}

		}

		destroyed.forEach(function (id) {
			var node = me.getNodeById("AddressBook-" + id);
			if (node) {
				node.destroy();
			}
		});
	},

	onGroupChanges: function (entityStore, added, changed, destroyed) {

		if (this.getLoader().loading) {
			return;
		}

		var me = this, groupId;
		for (groupId in changed) {
			var nodeId = "AddressBookGroup-" + groupId;
			me.getNodeById(nodeId).setText(changed[groupId].name);
		}
		;

		destroyed.forEach(function (groupId) {
			me.getNodeById("AddressBookGroup-" + groupId).destroy();
		});


	},

	showAddressBookMoreMenu: function (node, e) {
		if (!this.addressBookMoreMenu) {
			this.addressBookMoreMenu = new Ext.menu.Menu({
				items: [{
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							var dlg = new go.modules.community.addressbook.AddressBookDialog();
							dlg.load(this.addressBookMoreMenu.data.id).show();
						},
						scope: this
					}, {
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								go.Stores.get("AddressBook").set({destroy: [this.showAddressBookMoreMenu.entity.id]});
							}, this);
						},
						scope: this
					},
					"-",
					{
						iconCls: "ic-group",
						text: t("Add group"),
						handler: function () {
							var dlg = new go.modules.community.addressbook.GroupDialog({
								formValues: {
									addressBookId: this.addressBookMoreMenu.data.id
								}
							});
							dlg.show();
						},
						scope: this
					}, {
						iconCls: 'ic-share',
						text: t("Share"),
						handler: function () {
							var shareWindow = new go.modules.core.core.ShareWindow({
								title: t("Share") + ": " + this.addressBookMoreMenu.data.name
							});

							shareWindow.load(this.addressBookMoreMenu.data.aclId).show();
						},
						scope: this
					}]
			});
		}
		this.addressBookMoreMenu.data = node.attributes.data;
		this.addressBookMoreMenu.showAt(e.getXY());
	},

	showGroupMoreMenu: function (node, e) {
		if(!this.groupMoreMenu) {
			this.groupMoreMenu = new Ext.menu.Menu({									
				items: [{
						iconCls: 'ic-edit',
						text: t("Edit"),
						handler: function () {
							var dlg = new go.modules.community.addressbook.GroupDialog();
							dlg.load(this.groupMoreMenu.data.id).show();
						},
						scope: this
					}, {
						itemId: "delete",
						iconCls: 'ic-delete',
						text: t("Delete"),
						handler: function () {
							Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
								if (btn !== "yes") {
									return;
								}
								go.Stores.get("AddressBookGroup").set({destroy: [this.groupMoreMenu.data.id]});
							}, this);
						},
						scope: this
					}]
			});
		}
		
		this.groupMoreMenu.data = node.attributes.data;
		this.groupMoreMenu.showAt(e.getXY());
	},
	
	selectGroup : function(groupId) {
		var s = go.Stores.get("Contact");
		s.query({
			filter: {groupId: groupId, hasEmailAddresses: true}
		}, function(response) {			
			s.get(response.ids, function(contacts) {
				this.selectHandler.call(this.scope || this, contacts);
			}, this);
		}, this);
	},
	
	selectAddressBook : function(addressBookId) {
		var s = go.Stores.get("Contact");
		s.query({
			filter: {addressBookId: addressBookId, hasEmailAddresses: true}
		}, function(response) {			
			s.get(response.ids, function(contacts) {
				this.selectHandler.call(this.scope || this, contacts);
			}, this);
		}, this);
	}
});
