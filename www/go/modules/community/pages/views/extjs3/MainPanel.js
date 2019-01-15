go.modules.community.pages.MainPanel = Ext.extend(go.panels.ModulePanel, {
    layout: "border",
    siteId: '',
    pageId: '',
    siteSlug: '',
    pageSlug: '',
    //used in routing and redirecting in MainLayout.js
    shouldRedirect: true,

    initComponent: function () {

	this.content = new go.modules.community.pages.PageContent({
	    region: "center",
	    padding: '1% 5% 2% 2%',
	});
	this.treeArea = new go.modules.community.pages.SiteTreePanel({
	    region: "west",
	    width: dp(250),
	    //toggleFunction: this.disableButtons
	});

	this.items = [
	    this.content,
	    this.treeArea
	];

	this.tbar = new Ext.Toolbar({
	    items: [
		{
		    itemId: 'tbarAddBtn',
		    iconCls: 'ic-add',
		    tooltip: t('Add'),
		    handler: function (e, toolEl) {
			this.addPage();
		    },
		    scope: this
		}, {
		    itemId: 'tbarDelBtn',
		    iconCls: 'ic-delete',
		    tooltip: t('Delete current page'),
		    handler: function (e, toolEl) {
			Ext.MessageBox.confirm(t("Confirm delete"), t("Are you sure you wish to delete the currently opened page?"), function (btn) {
			    if (btn != "yes") {
				return;
			    }
			    selectedId = this.pageId;
			    go.Stores.get("Page").set({
				destroy: [selectedId]
			    });
			    go.Router.goto(this.siteSlug);
			}, this);
		    },
		    scope: this
		}, '->',
//		{
//		    xtype: "tbsearch"
//		},
		{
		    itemId: 'tbarEditBtn',
		    iconCls: 'ic-edit',
		    tooltip: t('Edit'),
		    handler: function (e, toolEl) {
			this.editPage(this.pageId);
		    },
		    scope: this
		}
	    ]
	});
	//navigate to a different page when selecting a node.
	this.treeArea.getSelectionModel().on('selectionchange', function (sm, node) {
	    if (node) {
		if(!node.isExpanded()){
		node.parentNode.collapseChildNodes(true);
		node.expand();
		}
		go.Router.goto(this.siteSlug + '\/view\/' + node.attributes.entitySlug)
	    }
	    
	}, this);
	this.treeArea.on('toggleButtons',function(bool, inclAddButton){
	    this.disableButtons(bool,inclAddButton)
	},this);
	go.modules.community.pages.MainPanel.superclass.initComponent.call(this);


    },
    addPage: function () {
	var dlg = new go.modules.community.pages.PageDialog({
	    siteId: this.siteId,
	    newPage: true
	});
	dlg.show();

    },

    editPage: function (id) {
	var dlg = new go.modules.community.pages.PageDialog({
	    siteId: this.siteId,
	    newPage: false
	});
	dlg.on('pageChanged', function (pageId, scope) {
	    tree = this.treeArea.siteTree;
	    tree.getNodeById("page-" + pageId).reload();
	}, this, {single: true});
	dlg.load(id).show();
    },

    //sets the site id for all relevant panels
    setSiteId: function (siteId) {
	//console.log('set site id to: ' + siteId)
	this.siteId = siteId;
	this.treeArea.setSiteId(siteId);
    },

    //updates the page id for all relevant panels
    navigateToPage: function (pageId, pageSlug) {
	//console.log('set page id to: ' + pageId)
	this.pageId = pageId;
	this.pageSlug = pageSlug;
	if (pageId) {
	    this.content.currentPage = this.pageId;
	    //acl check here
	    this.disableButtons(false, false);
	    //else
	    //this.disableButtons(true,true);
	    
		this.treeArea.siteTree.expandPath();
	} else {
	    this.content.showEmptyPage();
	    //acl check here
	    this.disableButtons(true, false);
	}
    },
    //toggles the edit and delete buttons. Optionally also disables the add button.
    disableButtons: function (bool, disableAddBtn) {
	this.getTopToolbar().getComponent('tbarDelBtn').setDisabled(bool)
	this.getTopToolbar().getComponent('tbarEditBtn').setDisabled(bool)
	if (disableAddBtn) {
	    this.getTopToolbar().getComponent('tbarAddBtn').setDisabled(bool)
	}
    }
});