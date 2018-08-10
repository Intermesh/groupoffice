go.modules.community.addressbook.AddressBookTree = Ext.extend(Ext.tree.TreePanel, {

	loader: new go.modules.community.addressbook.TreeLoader({
		baseAttrs: {
			iconCls: 'ic-account-box'
		},
		entityStore: go.Stores.get("Addressbook")
	}),
	root: {
		nodeType: 'async',
		draggable: false,
		id: null
	},
	rootVisible: false,
	
	currentAddressBookId: null,
	
	initComponent: function () {

		go.modules.community.addressbook.AddressBookTree.superclass.initComponent.call(this);

		this.getSelectionModel().on("selectionchange", function (sm, node) {
			
			
			if(!node || node.id == "all"){
				if(this.addressBookMoreBtn) {
					this.addressBookMoreBtn.hide();
				}
				
				if(this.groupMoreBtn) {
					this.groupMoreBtn.hide();
				}
				return;
			}

			
			if(node.attributes.isAddressBook) {
				
				if (!this.addressBookMoreBtn) {
					this.initAddressBookMoreBtn();
				}
				if(this.groupMoreBtn) {
					this.groupMoreBtn.hide();
				}
				this.addressBookMoreBtn.show();
				this.currentAddressBookId = node.attributes.entity.id;
				this.addressBookMoreBtn.getEl().alignTo(node.getUI().getEl(), 'tr-tr');
				this.addressBookMoreBtn.entity = node.attributes.entity;
	//			this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
	//		this.moreMenu.getComponent("share").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);


				
			} else
			{
				if(this.addressBookMoreBtn) {
					this.addressBookMoreBtn.hide();
				}
				if (!this.groupMoreBtn) {
					this.initGroupMoreBtn();
				}
				this.groupMoreBtn.show();
				this.groupMoreBtn.getEl().alignTo(node.getUI().getEl(), 'tr-tr');
				this.groupMoreBtn.entity = node.attributes.entity;
	//			this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
	//		this.moreMenu.getComponent("share").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);


				
			}
		}, this);
		
		this.getRootNode().on("load", function() {
			go.Stores.get("AddressBook").on('changes', this.onAddressBookChanges, this);
			go.Stores.get("AddressBookGroup").on('changes', this.onGroupChanges, this);

			this.on("destroy", function() {
				go.Stores.get("Addressbook").un('changes', this.onAddressBookChanges, this);
				go.Stores.get("AddressBookGroup").un('changes', this.onGroupChanges, this);
			});
		}, this, {single: true});
	},
	

	onAddressBookChanges : function(entityStore, added, changed, destroyed) {		
		debugger;
		if(this.getLoader().loading) {
			return;
		}
		if(added.length) {
			this.getRootNode().reload();
			return;
		}
		
		var me = this;
		
		changed.forEach(function(id) {
			var ab = go.Stores.get("AddressBook").data[id], 
			nodeId = "addressbook-" + ab.id, 
			node = me.getNodeById(nodeId);
			
			if(node) {
				node.attributes.entity = ab;
				delete node.attributes.children;
				node.reload();
			}
		});
		
		destroyed.forEach(function(id) {
			var node = me.getNodeById("addressbook-" + id);
			if(node) {
				node.destroy();
			}
		});
	},
	
	onGroupChanges : function(entityStore, added, changed, destroyed) {		
		
		if(this.getLoader().loading) {
			return;
		}
		
		var me = this;
		changed.forEach(function(groupId) {
			var nodeId = "group-" + groupId;			
			me.getNodeById(nodeId).setText(go.Stores.get("AddressBookGroup").data[groupId].name);
		});
		
		destroyed.forEach(function(groupId) {
			me.getNodeById("group-" + groupId).destroy();
		});
		
		
	},


	initAddressBookMoreBtn: function () {
		this.addressBookMoreBtn = new Ext.Button({
			renderTo: this.getEl(),
			cls: 'go-more-button-over',
			iconCls: 'ic-more-vert',
			menu: [{
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function() {
						var dlg = new go.modules.community.addressbook.AddressBookDialog();
						dlg.load(this.addressBookMoreBtn.entity.id).show();
					},
					scope: this				
				}, {
					itemId: "delete",
					iconCls: 'ic-delete',
					text: t("Delete"),
					handler: function () {
						Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you want to delete this item?"), function (btn) {
							if (btn != "yes") {
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
					handler: function() {
						var dlg = new go.modules.community.addressbook.GroupDialog({
							formValues: {
								addressBookId: this.currentAddressBookId
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
							title: t("Share") + ": " + this.addressBookMoreBtn.entity.name
						});

						shareWindow.load(this.addressBookMoreBtn.entity.aclId).show();
					},
					scope: this
				}]
		});
	},
	
	initGroupMoreBtn: function () {
		this.groupMoreBtn = new Ext.Button({
			renderTo: this.getEl(),
			cls: 'go-more-button-over',
			iconCls: 'ic-more-vert',
			menu: [{
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function() {
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
							if (btn != "yes") {
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
