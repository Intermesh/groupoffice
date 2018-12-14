go.modules.community.pages.MainPanel = Ext.extend(go.panels.ModulePanel, {
    layout: "border",
    siteId: '',
    pageId: '',
    siteSlug: '',
    //used in routing and redirecting in MainLayout.js
    shouldRedirect: true,
    
    initComponent: function () {

	this.content = new go.modules.community.pages.PageContent({
	    region: "center",
	    padding: '1% 5% 2% 2%',
	});
	this.tree = new go.modules.community.pages.SiteTreePanel({
	    region: "west",
	    width: dp(250),
	});

	this.items = [
	    this.content,
	    this.tree
	];

	this.tbar = new Ext.Toolbar({
	    items: [
		{
		    iconCls: 'ic-add',
		    tooltip: t('Add'),
		    handler: function (e, toolEl) {
			this.addPage();
		    },
		    scope: this
		},		{
		    iconCls: 'ic-delete',
		    tooltip: t('Delete current page'),
		    handler: function (e, toolEl) {
			Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you wish to delete the current page?"), function (btn) {
			    if (btn != "yes") {
				return;
			    }
			    selectedId = this.pageId;
			    go.Stores.get("Page").set({
			    destroy: [selectedId]
			    });
			    //todo:
			    //delete treepanel node as well.
			}, this);
			go.Router.goto(this.siteSlug)
		    },
		    scope: this
		}, '->',
		{
		    xtype: "tbsearch"
		},
		{
		    iconCls: 'ic-edit',
		    tooltip: t('Edit'),
		    handler: function (e, toolEl) {
			this.editPage(this.pageId);
		    },
		    scope: this
		}
	    ]
	});	
	
	this.tree.getSelectionModel().on('selectionchange', function(sm, node){
	    if(node){
		node.expand();
		go.Router.goto(this.siteSlug + '\/view\/' + node.attributes.entitySlug)
	    }
	}, this);
	go.modules.community.pages.MainPanel.superclass.initComponent.call(this);


    },
    addPage: function () {
	var dlg = new go.modules.community.pages.PageDialog({
	    siteId: this.siteId
	});
	dlg.show();
    },

    editPage: function (id) {
	var dlg = new go.modules.community.pages.PageDialog({
	    siteId: this.siteId
	});
	dlg.load(id).show();
    },

    //sets the site id for all relevant panels
    setSiteId: function(siteId){
	console.log('set site id to: '+siteId)
	this.siteId = siteId;
	this.tree.currentSiteId = this.siteId;
	this.tree.setLoaderSiteId(siteId);
    },
    
    //updates the page id for all relevant panels
    navigateToPage: function(pageId){
	console.log('set page id to: '+pageId)
	this.pageId = pageId;
	this.content.currentPage = this.pageId;
    }

});