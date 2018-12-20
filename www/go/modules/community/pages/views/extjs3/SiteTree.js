go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel, {
    layout: 'fit',
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

    initComponent: function () {
	go.modules.community.pages.SiteTree.superclass.initComponent.call(this);
	//sorter is required to sort the pages based on sort order.
	new Ext.tree.TreeSorter(this, {
	    sortType: function (value, node) {
		return node.attributes.sortOrder;
	    }
	});
    },
    
    initiateRootNode: function(){
	newRoot = {
	nodeType: 'async',
	text: 'Root',
	draggable: false,
	id: null,
	expanded: true
	};
	this.setRootNode(newRoot);
    },
})