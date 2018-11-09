go.modules.community.pages.SitePanel = Ext.extend(go.panels.ModulePanel, {
        layout: "border",
	siteId: 1,
        initComponent : function() {
	    
		this.content = new go.modules.community.pages.PageContent({
		    region:"center",
		    padding: '1% 5% 0px 2%',
                });
		this.tree = new go.modules.community.pages.SiteTreePanel({
		    region:"west",
		    width:dp(250),
		    currentSiteId: this.siteId
		});
		
                this.items = [
		    this.content,
		    this.tree
                ];
		
		this.tbar = new Ext.Toolbar({
		items:[
		{
		iconCls: 'ic-add',
		tooltip: t('Add'),
	    	handler: function (e, toolEl) {this.addPage(); },
		scope:this
		},'->',
		{
		  xtype: "tbsearch"  
		},
		{
		iconCls: 'ic-edit',
		tooltip: t('Edit'),
	    	handler: function (e, toolEl) {this.editPage(2); },
		scope:this
		}
	    ]
	    })


                go.modules.community.pages.SitePanel.superclass.initComponent.call(this);

                
        },
	addPage: function(){
	    var dlg = new go.modules.community.pages.PageDialog({
		siteId: this.siteId
	    });
	    dlg.show();
	},
	
	editPage: function(id){
	    var dlg = new go.modules.community.pages.PageDialog({
		siteId: this.siteId
	    });
	    dlg.load(id).show();
	}
	
});



