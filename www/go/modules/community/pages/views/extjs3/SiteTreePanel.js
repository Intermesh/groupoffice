go.modules.community.pages.SiteTreePanel = Ext.extend(Ext.Panel, {
    layout: "card",
    activeItem: 0,
    buttonAlign: 'left',
    currentSiteId: '',
    split: true,
    initComponent: function () {
	this.items = [
	    this.siteTree = new go.modules.community.pages.SiteTree({
		loader: new go.modules.community.pages.SiteTreeLoader({
		    baseAttrs: {
			iconCls: 'ic-label'
		    },
		    entityStore: go.Stores.get("Page"),
		}),

	    }),
	    this.siteTreeEdit = new go.modules.community.pages.SiteTreeEdit({
	    }),
	];
	this.reorderButton = new Ext.Button({
	    iconCls: 'ic-swap-vert',
	    tooltip: t('Reorder'),
	    handler: function (b, e) {
		b.setVisible(false);
		this.saveButton.setVisible(true);
		this.siteTreeEdit.store.load();
		this.fireEvent('toggleButtons', true, true);
		this.changePanel(this.siteTreeEdit.getId());
		this.siteTreeEdit.focus();
	    },
	    scope: this
	});
	this.saveButton = new Ext.Button({
	    iconCls: 'ic-save',
	    tooltip: t('Save'),
	    hidden: true,
	    handler: function (b, e) {
		b.setVisible(false);
		this.saveSortOrder();
		this.reorderButton.setVisible(true);
		this.fireEvent('toggleButtons', false, true);
		this.changePanel(this.siteTree.getId());
	    },
	    scope: this
	});

	this.fbar = new Ext.Toolbar({
	    items: [
		this.reorderButton,
		this.saveButton
//			, '->',
//		{
//		    iconCls: 'ic-get-app',
//		    tooltip: t('Download'),
//		    handler: function (e, toolEl) {
//			var a = ["test"];
//			this.downloadPDF();
//		    },
//		    scope: this
//		}
	    ]
	});
	this.addEvents('toggleButtons');

	this.on("afterrender", function () {
	    this.siteTree.getLoader().on('load', function () {
		this.siteTree.getLoader().entityStore.on('changes', this.onChanges, this);
	    }, this, {single: true});

	}, this);
	go.modules.community.pages.SiteTreePanel.superclass.initComponent.call(this);
    },
    changePanel: function (panel) {
	this.layout.setActiveItem(panel);
    },

//    downloadPDF: function (id) {
//    },

    reloadTree: function () {
	if (!this.siteTree.getLoader().loading) {
	    this.siteTree.getRootNode().reload();
	} else {
	    console.warn('tree is already loading');
	}
    },
    setSiteId: function (siteId) {
	this.currentSiteId = siteId;
	this.siteTree.getLoader().siteId = siteId;
	this.siteTree.initiateRootNode();
	this.siteTreeEdit.siteId = siteId;
    },
    getSelectionModel: function () {
	return this.siteTree.getSelectionModel();
    },

    onChanges: function (entityStore, added, changed, destroyed) {
//	console.log('added');
//	console.log(added);
//	console.log('changed');
//	console.log(changed);
//	console.log('destroyed');
//	console.log(destroyed);
	var me = this, reload = false, id;
	//for each added
	for (id in added) {

	    if (!me.siteTree.getRootNode().findChild('id', id)) {
		me.reloadTree();
		return;
	    }
	}
	//for each changed
	//Currently only used to update the node name and not to reload the node itself.
	//The event 'pageChanged' is used in the mainpanel and PageDialog to reload the node.
	//This is because changed also triggers when first expanding the node.
	for (id in changed) {
	    nodeId = 'page' + "-" + id,
		    node = me.siteTree.getNodeById(nodeId);
	    if (node) {
		node.attributes.data = changed[id];
		if (changed[id].pageName) {
		    node.setText(changed[id].pageName);
		}
	    }
	}
	//foreach destroyed
	destroyed.forEach(function (id) {
	    var node = me.siteTree.getNodeById("page-" + id);
	    if (node) {
		node.destroy();
		me.reloadTree();
	    }
	});
    },

    saveSortOrder: function () {
	var records = this.siteTreeEdit.store.getRange();
	var update = {};
	for (var i = 0, l = records.length; i < l; i++) {
	    update[records[i].data.id] = {sortOrder: i + 1};
	}
	go.Stores.get("Page").set({
	    update: update
	}, function () {
	    this.reloadTree();
	}, this);
    }
});