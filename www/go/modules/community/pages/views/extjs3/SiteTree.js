go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel, {
    layout: 'fit',
    enableDD: false,
    border: true,
    root: {},
    rootVisible: false,
    autoScroll: true,

    initComponent: function () {
	go.modules.community.pages.SiteTree.superclass.initComponent.call(this);
	//sorter is required to sort the pages based on sort order.
	new Ext.tree.TreeSorter(this, {
	    sortType: function (value, node) {
		return node.attributes.sortOrder;
	    }
	});
	GO.mainLayout.tabPanel.on('tabchange', function (tabPanel, panel) {
	    if (panel.siteId !== '' && panel.siteId == this.loader.siteId) {
		panel.treeArea.siteTree.onTabChange();
	    }
	}, this);

	GO.mainLayout.tabPanel.on('beforetabchange', function (panel, newTab, currentTab) {
	    if (currentTab && currentTab.treeArea) {
		currentTab.treeArea.siteTree.onBeforeTabChange();
	    }
	}, this);
    },

    //create and load in a new root node
    initiateRootNode: function () {
	newRoot = {
	    nodeType: 'async',
	    text: 'Root',
	    draggable: false,
	    expanded: true
	};
	this.setRootNode(newRoot);
	this.rootVisible = false;
	this.root.on('load', function (node) {
	    if (node.childNodes.length > 0) {
		this.expandPath();
	    }
	}, this)
    },

    //before changing sites this will try to close the sites treenodes and clear the selection.
    onBeforeTabChange: function () {
	if (this.root.isLoaded() && this.getSelectionModel().getSelectedNode()) {
	    this.root.collapseChildNodes(true);
	    this.getSelectionModel().clearSelections();
	}
    },
    onTabChange: function () {
	this.expandPath();
    },

    //attempts to determine which nodes should be opened based on the current url.
    expandPath: function () {
	slugs = go.Router.getPath().split('/');
	pageSlug = slugs[2];
	headerSlug = slugs[3];
	pageNode = this.root.findChild('entitySlug', pageSlug);
	if (pageNode) {
	    if (pageNode.isLoaded()) {
		this.cascadeExpand(pageSlug, headerSlug, pageNode)
	    } else {
		this.root.on('load', function (node) {
		    if (pageNode) {
			pageNode.expand()
			pageNode.on('load', function (node) {
			    this.cascadeExpand(pageSlug, headerSlug, node)
			}, this);
		    }
		}, this)
	    }
	}
    },

    cascadeExpand: function (pageSlug, headerSlug, pageNode) {
	//run through all the nodes in a page to determine which should be selected and expanded.
	pageNode.cascade(function (node) {
	    node.expand();
	    childNode = node.findChild('id', 'header-' + pageSlug + '/' + headerSlug)
	    if (node.id === 'header-' + pageSlug + '/' + headerSlug || (node == pageNode && !headerSlug)) {
		node.select();
		return false
	    } else if (childNode) {
		childNode.select();
		return false
	    }
	    ;
	    node.collapse();
	})
	//Check if anything is selected. If there isnt, the header in the url likely doesnt exist.
	//In this case just the page node will be selected.
	if (!this.getSelectionModel().getSelectedNode()) {
	    pageNode.select();
	}
    }
})