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
	autoScroll:true,
	
	initComponent : function() {
		
		go.modules.community.addressbook.AddressBookTree.superclass.initComponent.call(this);
		
		
		
		this.on("click", function(node, e) {
			if(!this.moreButton) {
				this.initMoreButton();
			}
			console.log(node);
			this.moreButton.getEl().alignTo(node.getUI().getEl(), 'tr-tr');
			this.moreButton.entity = node.attributes.entity;
//			this.moreMenu.getComponent("edit").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
//		this.moreMenu.getComponent("share").setDisabled(record.get("permissionLevel") < GO.permissionLevels.manage);
		
		
			this.moreButton.show();
		}, this);
	},
	
	initMoreButton : function() {
		this.moreButton = new Ext.Container({
			xtype:"box",
			style: "position: absolute;width:72px;height:35px;padding-right: 7px",
		
			renderTo: this.getEl(),
			items: [{
				xtype: "box",
				style: "height: 100%;float:left; background: linear-gradient(to right, transparent, #e6e6e6);width:30px",
		
			},new Ext.Button({
				style: "background: #e6e6e6;float: right;",
				iconCls: 'ic-more-vert',
				menu: [{
						iconCls: 'ic-edit',
						text: t("Edit")
				},{
						iconCls: 'ic-delete',
						text: t("Delete")
				},
				"-",
				{
					iconCls: "ic-group",
						text: t("Add group")
				},{
					iconCls: 'ic-share',
					text: t("Share"),
					handler: function() {
						var shareWindow = new go.modules.core.core.ShareWindow({
							title: t("Share") + ": " + this.moreButton.entity.name
						});

						shareWindow.load(this.moreButton.entity.aclId).show();
					},
					scope: this	
				}]
			})]
	});
	}
});
