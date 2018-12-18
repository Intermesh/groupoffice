go.modules.community.pages.SiteTreePanel = Ext.extend(Ext.Panel, {
    layout: "card",
    activeItem: 0,
    buttonAlign: 'left',
    currentSiteId: '',
    split: true,
    autoScroll: true,
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
		this.changePanel(this.siteTreeEdit.getId());
	    },
	    scope: this
	});
	this.saveButton = new Ext.Button({
	    iconCls: 'ic-save',
	    tooltip: t('Save'),
	    hidden: true,
	    handler: function (b, e) {
		b.setVisible(false);
		this.reorderButton.setVisible(true);
		this.changePanel(this.siteTree.getId());
	    },
	    scope: this
	});

	this.fbar = new Ext.Toolbar({
	    items: [
		this.reorderButton,
		this.saveButton
			, '->',
		{
		    iconCls: 'ic-get-app',
		    tooltip: t('Download'),
		    handler: function (e, toolEl) {
			//console.log("download pdf");
			var a = ["test"];
			this.downloadPDF();
		    },
		    scope: this
		}]
	});

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
    downloadPDF: function (id = this.currentSiteId) {
	this.reloadTree();
	//temp used to generate the treepanel content at the click of a currently unused button.
	var params = {siteId: id};
	go.Jmap.request({
	    method: "page/getTree",
	    params: params,
	    scope: this,
	    callback: function (options, success, response) {
		console.log(response);
	    }
	});
    },
    reloadTree: function () {
	console.log('reloading');
	if (!this.siteTree.getLoader().loading) {
	    this.siteTree.getRootNode().reload();
	} else {
	    console.log('tree is already loading');
	}
    },
    setSiteId: function (siteId) {
	this.currentSiteId = siteId;
	this.siteTree.getLoader().siteId = siteId;
	this.siteTreeEdit.siteId = siteId;
    },
    getSelectionModel: function () {
	return this.siteTree.getSelectionModel();
    },

    onChanges: function (entityStore, added, changed, destroyed) {

	var me = this, reload = false, id;
	//for each added
	for (id in added) {

	    if (!me.siteTree.getRootNode().findChild('id', id)) {

		me.reloadTree();
		return;
	    }
	}
	//for each changed
	for (id in changed) {
	    nodeId = "page-" + id,
		    node = me.siteTree.getNodeById(nodeId);
	    if (node) {
		node.attributes.data = changed[id];

		if (changed[id].name) {
		    node.setText(changed[id].name);
		}
		node.reload();
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
    }
})