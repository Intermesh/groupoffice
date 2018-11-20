go.modules.community.pages.SiteTree = Ext.extend(Ext.tree.TreePanel,{

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
    
    initComponent : function() {
	go.modules.community.pages.SiteTree.superclass.initComponent.call(this);
    }
    
})