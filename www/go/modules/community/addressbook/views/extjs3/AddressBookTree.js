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
	
	initComponent: function () {

		go.modules.community.addressbook.AddressBookTree.superclass.initComponent.call(this);

		this.getSelectionModel().on("selectionchange", function (sm, node) {
			if (!this.moreButton) {
				this.initMoreButton();
			}
			
			if(!node){
				this.moreButton.hide();
				return;
			}
			
			this.moreButton.getEl().alignTo(node.getUI().getEl(), 'tr-tr');
			this.moreButton.entity = node.attributes.entity;
//			this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
//		this.moreMenu.getComponent("share").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);


			this.moreButton.show();
		}, this);
		
		this.getRootNode().on("load", function() {
			go.Stores.get("Addressbook").on('changes', this.onChanges, this);

			this.on("destroy", function() {
				go.Stores.get("Addressbook").un('changes', this.onChages, this);
			});
		}, this, {single: true});
	},
	

	onChanges : function(entityStore, added, changed, destroyed) {		
		this.getRootNode().reload();
	},


	initMoreButton: function () {
		this.moreButton = new Ext.Button({
			renderTo: this.getEl(),
			cls: 'go-more-button-over',
			iconCls: 'ic-more-vert',
			menu: [{
					iconCls: 'ic-edit',
					text: t("Edit"),
					handler: function() {
						var dlg = new go.modules.community.addressbook.AddressBookDialog();
						dlg.load(this.moreButton.entity.id).show();
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
							go.Stores.get("AddressBook").set({destroy: [this.moreButton.entity.id]});
						}, this);
					},
					scope: this
				},
				"-",
				{
					iconCls: "ic-group",
					text: t("Add group")
				}, {
					iconCls: 'ic-share',
					text: t("Share"),
					handler: function () {
						var shareWindow = new go.modules.core.core.ShareWindow({
							title: t("Share") + ": " + this.moreButton.entity.name
						});

						shareWindow.load(this.moreButton.entity.aclId).show();
					},
					scope: this
				}]
		});
	}
});
