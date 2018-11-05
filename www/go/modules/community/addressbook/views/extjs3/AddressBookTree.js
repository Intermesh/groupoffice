/* global go, Ext */

go.modules.community.addressbook.AddressBookTree = Ext.extend(Ext.tree.TreePanel, {

	loader: new go.modules.community.addressbook.TreeLoader(),
	root: {
		nodeType: 'async',
		draggable: false,
		id: null,
		uiProvider: Ext.tree.RootTreeNodeUI // needed to make "rootVisible" work
	},
	rootVisible: false,

	currentAddressBookId: null,

	initComponent: function () {

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
				this.showAddressBookMoreMenu(node, e);


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
				node.attributes.entity = changed[id];

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
								go.Stores.get("AddressBook").set({destroy: [this.addressBookMoreBtn.entity.id]});
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

	initGroupMoreBtn: function () {
		this.groupMoreBtn = new Ext.Button({
			renderTo: this.getEl(),
			cls: 'go-more-button-over',
			iconCls: 'ic-more-vert',
			menu: [{
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function () {
						var dlg = new go.modules.community.addressbook.GroupDialog();
						dlg.load(this.groupMoreBtn.entity.id).show();
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
							go.Stores.get("AddressBookGroup").set({destroy: [this.groupMoreBtn.entity.id]});
						}, this);
					},
					scope: this
				}]
		});
	}
});
