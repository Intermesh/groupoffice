GO.site.MainPanel = function(config){
	
	if(!config)
		config = {};
	
	this.centerPanel = new Ext.Panel({
		region:'center',
		border:true,
		layout:'card',
		layoutConfig:{ 
			layoutOnCardChange:true
		},
		items:[
			this.contentPanel = new GO.site.ContentPanel({
				cls:'go-white-bg',
				parentPanel:this
			})
			]
	}); 
	
	this.treePanel = new GO.site.SiteTreePanel({
		region:'west',
		width:300,
		border:true,
		contentPanel:this.contentPanel,
		mainPanel:this
	});

	config.layout='border';
	
	
	this.eastPanel = new Ext.Panel({
		region:'east',
		id:'site-cheat-sheet',
		width:500,
		collapsible:true,
		title:'Cheat sheet',
		autoScroll:true,
		split:true,
		autoLoad : {
        url : GO.url('site/site/cheatSheet')
    }
	});
	
	config.items=[
		this.treePanel,
		this.centerPanel,
		this.eastPanel
	];
	
	this.reloadButton = new Ext.Button({
		iconCls: 'btn-refresh',
		itemId:'refresh',
		text: t("Refresh"),
		cls: 'x-btn-text-icon'
	});
	
	this.reloadButton.on("click", function(){
		this.rebuildTree();
	},this);
	
	config.tbar=new Ext.Toolbar({
			cls:'go-head-tb',
			items: [{
					xtype:'htmlcomponent',
					html:t("Website Manager", "site"),
					cls:'go-module-title-tbar'
				},this.reloadButton
			]
	});
	
//	if(go.Modules.isAvailable("legacy", "files")) {
//		this.fileBrowserButton = new GO.files.FileBrowserButton({
//			model_name:"GO\\Site\\Model\\Site"
//		});
//		config.tbar.insertButton(2,this.fileBrowserButton);
//		this.treePanel.on('click', function(node,event){
//			this.fileBrowserButton.setId(node.attributes['site_id']);
//		}, this);
//	}
	
	GO.site.MainPanel.superclass.constructor.call(this, config);
}

Ext.extend(GO.site.MainPanel, Ext.Panel,{
	
	showSiteDialog: function(site_id){
		if(!this.siteDialog){
			this.siteDialog = new GO.site.SiteDialog();
			this.siteDialog.on('hide', function(){
				this.rebuildTree();
			},this);
		}
		
		this.siteDialog.show(site_id);
	},

	rebuildTree: function(select){
		
		var selectedNode = this.treePanel.getSelectionModel().getSelectedNode();
		this.treePanel.getRootNode().reload();
		
		if(select)
			this.treePanel.getSelectionModel().select(selectedNode); 
	}
});

// GO.moduleManager.addModule('site', GO.site.MainPanel, {
// 	title : t("Website Manager", "site"),
// 	iconCls : 'go-tab-icon-site'
// });

go.Modules.register("legacy", 'site', {
	mainPanel: GO.site.MainPanel,
	title : t("Website Manager", "calendar"),
	iconCls : 'go-tab-icon-site',
	entities: [{
		name: "Site"
		// customFields: {
		// 	fieldSetDialog: "GO.site.CustomFieldSetDialog"
		// }
	}]

});


GO.site.extractTreeNode = function(node){

	var siteId = false;
	var type = false;
	var type_up = false;
	var modelId = false;
	
	var parts = node.attributes.id.split('_');
		
	if(parts.length > 0){
		
		if(parts[0] === 'site'){ // It's the root site node
			siteId = parts[1];
			type = parts[0];
			
		} else {
			siteId = parts[0];
			type = parts[1];

			if(parts.length > 2)
				modelId = parts[2];
		}
		
		return {
			siteId	:	siteId,
			type		:	type,
			type_up	:	type.charAt(0).toUpperCase()+type.slice(1),
			modelId	:	modelId
		}
	}
		
	return false;
}
