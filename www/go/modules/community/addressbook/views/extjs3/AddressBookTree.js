go.modules.community.addressbook.AddressBookTree = Ext.extend(Ext.tree.TreePanel, {
	
	loader: new go.modules.community.addressbook.TreeLoader({
		baseAttrs: {
			iconCls: 'ic-account-box',
			//uiProvider:go.modules.community.files.FolderTreeNodeUI
		},
		entityStore: go.Stores.get("Addressbook")
	}),	
	root: {
			nodeType: 'async',
			draggable: false,
			id: null
	},
	rootVisible: false,
	autoScroll:true
});
