go.modules.community.pages.SiteTreePanel = Ext.extend(Ext.Panel,{
    layout:"card",
    activeItem: 0,
    buttonAlign: 'left',
    split: true,
//    autoScroll: true,
    initComponent : function() {
	this.items = [
	this.siteTree = new go.modules.community.pages.SiteTree({
	    itemId: 'siteTree'
	}),
	this.siteTreeEdit = new go.modules.community.pages.SiteTreeEdit({
	    itemId: 'siteTreeEdit'
	}),
	],
	this.fbar = new Ext.Toolbar({
	items: [{
		itemId:"reorderButton",
		iconCls: 'ic-swap-vert',
		tooltip: t('Reorder'),
	    	handler: function (b, e) {
		this.getFooterToolbar().getComponent('saveButton').setVisible(true);
		b.setVisible(false);
	    	this.changePanel('siteTreeEdit');
		},
		scope:this
		},{
		itemId:"saveButton",
		iconCls: 'ic-save',
		tooltip: t('Save'),
		hidden: true,
	    	handler: function (b, e) {
		b.setVisible(false);
		this.getFooterToolbar().getComponent('reorderButton').setVisible(true);
	    	this.changePanel('siteTree');
		},
		scope:this
		},'->',{
		iconCls: 'ic-get-app',
		tooltip: t('Download'),
	    	handler: function (e, toolEl) {
	    	console.log("download pdf");
		    }
		 }]
	})

	

	go.modules.community.pages.SiteTreePanel.superclass.initComponent.call(this);
    },
    changePanel: function(panel){
	this.layout.setActiveItem(panel);
    }
    
})