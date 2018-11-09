go.modules.community.pages.PageContent = Ext.extend(Ext.Panel,{
    layout:"fit",
    entityStore: go.Stores.get("Page"),
    autoScroll: true,
    currentPage: 2,
    initComponent : function() {
	this.html = 'Loading...'
	
	

	go.modules.community.pages.PageContent.superclass.initComponent.call(this);
	this.on("render", function() {
	    this.updateData();
	}, this);
	this.entityStore.on("changes", function() {this.updateData();}, this);
    },
        updateData : function() {
	    var pageContent = go.Stores.get("Page").get([this.currentPage], function(content){
	    this.update(content[0].content, false)
	},this)
    }
})