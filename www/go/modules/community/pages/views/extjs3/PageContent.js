go.modules.community.pages.PageContent = Ext.extend(Ext.Panel, {
    layout: "fit",
    entityStore: go.Stores.get("Page"),
    autoScroll: true,
    currentPage: '',
    initComponent: function () {
	this.html = t('Loading...');

	go.modules.community.pages.PageContent.superclass.initComponent.call(this);
	this.addEvents('contentLoaded');
	this.on("render", function () {
	    this.updateData();
	    this.entityStore.on("changes", function () {
		if (this.currentPage) {
		    this.updateData();
		}
	    }, this);
	}, this);
    },

    updateData: function () {
	if (this.currentPage) {
	    go.Stores.get("Page").get([this.currentPage], function (content) {
		if(content[0]){
		    this.update(content[0].content, false);
		    this.fireEvent('contentLoaded', this);
		}
	    }, this);
	}
    },

    showEmptyPage: function () {
	this.currentPage = null;
	this.update('<p><b>' + t("No page found to display.") + '</b></p>', false);
    }
})