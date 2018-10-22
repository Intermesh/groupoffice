go.modules.community.pages.MainPanel = Ext.extend(go.panels.ModulePanel, {
	layout:"fit",
        initComponent : function() {
	    this.sitepanel = new go.modules.community.pages.SitePanel()
	this.items = [
	    this.sitepanel
	];
	go.modules.community.pages.MainPanel.superclass.initComponent.call(this);
	}

});
