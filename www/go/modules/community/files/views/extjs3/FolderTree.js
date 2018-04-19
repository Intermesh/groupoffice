go.modules.community.files.FolderTree = Ext.extend(Ext.tree.TreePanel, {
	
	animate:true,
	enableDD:false,
	loader: new Ext.tree.TreeLoader(), // Note: no dataurl, register a TreeLoader to make use of createNode()
	lines: true,
	selModel: new Ext.tree.MultiSelectionModel(),
	containerScroll: false,
	
	initComponent: function () {
		
		// set the root node
    var root = new Ext.tree.AsyncTreeNode({
        text: 'Personal files',
        draggable:false,
//        id:'source',
        children: {}
    });
		
		go.modules.community.files.FolderTree.superclass.initComponent.call(this);
		

    this.setRootNode(root);
	}
	
});

// Custom treeloader with go.tree.treeloader(entity:....);