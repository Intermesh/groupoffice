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
		itemId: 'siteTree',
		loader: new go.modules.community.pages.SiteTreeLoader({
		    baseAttrs: {
			//iconCls: 'ic-web-asset'
			iconCls: 'ic-remove'
		    },
		    entityStore: go.Stores.get("Page"),
		}),

		//pass along a cb or event handler for changing page content
		//make sure to update the siteId filter when changing pages!

	    }),
	    this.siteTreeEdit = new go.modules.community.pages.SiteTreeEdit({
		itemId: 'siteTreeEdit'
	    }),
	],
		this.fbar = new Ext.Toolbar({
		    items: [{
			    itemId: "reorderButton",
			    iconCls: 'ic-swap-vert',
			    tooltip: t('Reorder'),
			    handler: function (b, e) {
				this.getFooterToolbar().getComponent('saveButton').setVisible(true);
				b.setVisible(false);
				this.siteTreeEdit.store.load();
				this.changePanel('siteTreeEdit');
			    },
			    scope: this
			},
			{
			    itemId: "saveButton",
			    iconCls: 'ic-save',
			    tooltip: t('Save'),
			    hidden: true,
			    handler: function (b, e) {
				b.setVisible(false);
				this.getFooterToolbar().getComponent('reorderButton').setVisible(true);
				this.changePanel('siteTree');
			    },
			    scope: this
			}, '->',
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
	    this.siteTreeEdit.siteId = this.currentSiteId;

	    this.siteTree.getLoader().siteId = this.currentSiteId;
	    this.siteTree.getLoader().on('load', function () {
		console.log('loaded');
		this.siteTree.getLoader().entityStore.on('changes', this.reloadTree, this);
	    }, this);
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
	console.log('reload');
	if (!this.siteTree.getLoader().loading) {
	    console.log('current siteId: ' + this.currentSiteId);
	    console.log('loader siteId: ' + this.siteTree.getLoader().siteId);
	    this.siteTree.getRootNode().reload();
	} else {
	    console.log('loading...');
	}
    }
})