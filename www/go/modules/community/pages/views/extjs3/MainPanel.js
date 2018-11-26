go.modules.community.pages.MainPanel = Ext.extend(go.panels.ModulePanel, {
    layout: "border",
    siteId: '1',
    pageId: '2',
    initComponent: function () {

	this.content = new go.modules.community.pages.PageContent({
	    region: "center",
	    padding: '1% 5% 0px 2%',
	});
	this.tree = new go.modules.community.pages.SiteTreePanel({
	    region: "west",
	    width: dp(250),
	    //currentSiteId: this.siteId
	    // add event listener to change the currently shown page, or pass a callback.
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
		}, '->',
		{
		    xtype: "tbsearch"
		},
		{
		    iconCls: 'ic-edit',
		    tooltip: t('Edit'),
		    handler: function (e, toolEl) {
			this.editPage(2);
		    },
		    scope: this
		}
	    ]
	});
	this.on("render", function () {
	    console.log(this.siteId);
	    console.log(this.pageId);
	    this.tree.currentSiteId = this.siteId;
	    this.content.currentPage = this.pageId;
	}, this);

	go.modules.community.pages.MainPanel.superclass.initComponent.call(this);
	//add events here


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
    
    setIds: function(pageId, siteId = this.siteId){
	this.siteId = siteId;
	this.pageId = pageId;
	    console.log(this.siteId);
	    console.log(this.pageId);
    }

});