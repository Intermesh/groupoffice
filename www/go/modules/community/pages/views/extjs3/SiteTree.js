go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel,{

    enableDD: false,
    border: true,
    
    root: {
	nodeType: 'async',
        text: 'Root',
        draggable: false,
        id: null,
	expanded: true
    },
    rootVisible: false,
    
    initComponent : function() {
	go.modules.community.pages.SiteTree.superclass.initComponent.call(this);
	
	//because the root node is not visible it will auto expand on render.
		this.root.on('expand', function (node) {
			//when expand is done we'll select the first node. This will trigger a selection change. which will load the grid below.
			this.getSelectionModel().select(node.firstChild);
		}, this);
    }
    
})