go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel, {

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

	new Ext.tree.TreeSorter(this, {
	    sortType: function (value, node) {
		return node.attributes.sortOrder;
	    }
	});

	this.root.on('expand', function (node) {
	    this.getSelectionModel().select(node.firstChild);
	}, this);
    },

})