go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel,{

    enableDD: false,
    border: true,
    useArrows: true,
    
    root: {
	nodeType: 'async',
        text: 'Root',
        draggable: false,
        id: null,
	expanded: true
    },
    
	rootVisible: true,
    initComponent : function() {
	    this.loader = new go.modules.community.pages.PagesTreeLoader({
		baseAttrs: {
			iconCls: 'ic-web_asset'
		},
		entityStore: go.Stores.get("Page")
	});
	go.modules.community.pages.SiteTree.superclass.initComponent.call(this);
    }
    
})