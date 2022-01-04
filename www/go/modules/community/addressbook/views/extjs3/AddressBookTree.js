/* global go, Ext */

go.modules.community.addressbook.AddressBookTree = Ext.extend(Ext.tree.TreePanel, {
	
	rootVisible: false,

	currentAddressBookId: null,

	readOnly: false,
	scope: null,

	autoHeight: true,
	
	initComponent: function () {

		if(this.readOnly === false) {
			this.loader = new go.modules.community.addressbook.TreeLoader();
		} else
		{
			this.loader = new go.modules.community.addressbook.TreeLoader({
				secondaryTextTpl: ''
			});
		}

		// this.selModel = new Ext.tree.MultiSelectionModel();
		
		this.root = {
			nodeType: 'groupoffice',
			draggable: false,
			id: null,
			uiProvider: Ext.tree.RootTreeNodeUI // needed to make "rootVisible" work
		};
		
		go.modules.community.addressbook.AddressBookTree.superclass.initComponent.call(this);


//		this.getSelectionModel().on("selectionchange", this.onSelectionChange, this);

		this.getRootNode().on("load", function () {
			go.Db.store("AddressBook").on('changes', this.onAddressBookChanges, this);
			go.Db.store("AddressBookGroup").on('changes', this.onGroupChanges, this);

			this.on("destroy", function () {
				go.Db.store("Addressbook").un('changes', this.onAddressBookChanges, this);
				go.Db.store("AddressBookGroup").un('changes', this.onGroupChanges, this);
			});
		}, this, {single: true});


		this.on("click", function (node, e) {

			if (e.target.tagName === "BUTTON") {

				if(!this.readOnly) {
					if(node.attributes.entity.name === "AddressBook") {
						this.showAddressBookMoreMenu(node, e);
					} else
					{
						this.showGroupMoreMenu(node, e);
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
		added.forEach((id) => {
			if (!this.findAddressbookNode(id)) {
				this.getRootNode().reload();
				return;
			}
		});

		entityStore.get(changed).then((result) => {
			result.entities.forEach((addressBook) => {
				const nodeId = "AddressBook-" + addressBook.id,
					node = this.getNodeById(nodeId);

				if (node) {
					node.attributes.data = addressBook;

					if (addressBook.name) {
						node.setText(addressBook.name);
					}

					if (addressBook.groups) {
						delete node.attributes.children;
						node.reload();
					}
				}
			});

		});

		destroyed.forEach( (id) => {
			const node = this.getNodeById("AddressBook-" + id);
			if (node) {
				node.destroy();
			}
		});
	},

	onGroupChanges: function (entityStore, added, changed, destroyed) {		
		if (this.getLoader().loading) {
			return;
		}

		const reloadAddressBookIds = [];

		entityStore.get(added).then((result) => {
			result.entities.forEach((group) => {
				reloadAddressBookIds.push(group.addressBookId);
			});
		}).then(() => {
			entityStore.get(changed).then((result) => {
				result.entities.forEach((group) => {
					const nodeId = "AddressBookGroup-" + group.id;
					const node = this.getNodeById(nodeId);
					if (node) {
						node.setText(group.name);
					} else {
						reloadAddressBookIds.push(group.addressBookId);
					}
				});

				reloadAddressBookIds.forEach((addressBookId) => {
					const abNode = this.getNodeById("AddressBook-" + addressBookId);
					delete abNode.attributes.children;
					abNode.reload();
				});

			})
		});

		destroyed.forEach((groupId) => {
			this.getNodeById("AddressBookGroup-" + groupId).destroy();
		});

	},
	
	showAddressBookMoreMenu: function (node, e) {
		if (!this.addressBookMoreMenu) {
			this.addressBookMoreMenu = new Ext.menu.Menu({
				items: [{
						itemId: "edit",
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
								go.Db.store("AddressBook").set({destroy: [this.addressBookMoreMenu.data.id]});
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
							dlg.on('submit', function(dlg, success, serverId) {
								this.getNodeById("AddressBook-" + this.addressBookMoreMenu.data.id).reload();
							}, this);
						},
						scope: this
					}]
			});
		}
		this.addressBookMoreMenu.data = node.attributes.data;
		this.addressBookMoreMenu.getComponent("edit").setDisabled(this.addressBookMoreMenu.data.permissionLevel < go.permissionLevels.manage);
		this.addressBookMoreMenu.getComponent("delete").setDisabled(this.addressBookMoreMenu.data.permissionLevel  < go.permissionLevels.manage);
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
								go.Db.store("AddressBookGroup").set({destroy: [this.groupMoreMenu.data.id]});
							}, this);
						},
						scope: this
					}]
			});
		}
		
		this.groupMoreMenu.data = node.attributes.data;
		this.groupMoreMenu.showAt(e.getXY());
	}
});
