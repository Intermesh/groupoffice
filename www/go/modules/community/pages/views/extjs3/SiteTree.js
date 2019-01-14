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
    },

    initiateRootNode: function () {
	newRoot = {
	    nodeType: 'async',
	    text: 'Root',
	    draggable: false,
	    expanded: true
	};
	this.setRootNode(newRoot);
	this.root.on('load', function (node) {
	    this.expandPath();
	}, this)
	debugger;
    },

    expandPath: function () {
	console.log('expandpath')
	slugs = go.Router.getPath().split('/');
	page = slugs[2];
	headerSlug = slugs[3];
	pageNode = this.root.findChild('entitySlug', page);
	if (pageNode) {
	    pageNode.on('load', function (node) {
		console.log('cascade')
		//run through all the nodes in a page to determine which should be selected and expanded.
		node.cascade(function (node) {
		    node.expand();
		    childNode = node.findChild('id', 'header-' + page + '/' + headerSlug)
		    if (node.id === 'header-' + page + '/' + headerSlug || (node == pageNode && !headerSlug)) {
			node.select();
			return false
		    } else if (childNode) {
			childNode.select();
			return false
		    }
		    ;
		    node.collapse();
		})
		//Check if anything is selected, if there isnt the header in the url likely doesnt exist.
		//In this case just the page node will be selected.
		if (!this.getSelectionModel().getSelectedNode()) {
		    pageNode.select();
		}
	    }, this);
	    if (pageNode.isLoaded()) {
		console.log('expand')
		pageNode.expand();
	    } else {
		this.root.on('load', function (node) {
		    console.log('onload expand')
		    pageNode.expand();
		}, this, {single: true});
	    }
	}
    },
    
    cascadeExpand: function(pageSlug, HeaderSlug, PageNode){
	
    }
})