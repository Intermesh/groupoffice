GO.site.HtmlEditorContentTreePanel = function (config){
	config = config || {};

	config.loader=new GO.base.tree.TreeLoader(
	{
		dataUrl: GO.url('site/content/contentTree'),
		baseParams:{site_id: 0},
		preloadChildren:true
	});
	
	config.loader.on('beforeload', function(){
		var el =this.getEl();
		if(el)
			el.mask(t("Loading..."));
	}, this);

	config.loader.on('load', function(){
		var el =this.getEl();
		if(el)
			el.unmask();
	}, this);
	
	Ext.applyIf(config, {
		layout:'fit',
		split:true,
		autoScroll:true,
		width: 200,
		animate:true,
		rootVisible:true,
		containerScroll: true,
		selModel:new Ext.tree.DefaultSelectionModel()
	});
	
	GO.site.HtmlEditorContentTreePanel.superclass.constructor.call(this, config);
}
	
Ext.extend(GO.site.HtmlEditorContentTreePanel, Ext.tree.TreePanel,{
	loadingDone : false,
	siteId : 0,

	setSiteId : function(site_id){
		this.siteId = site_id;
		this.getLoader().baseParams.site_id = this.siteId;
		this.rebuildTree();
	},
	
	isRootContentNode: function(node){
		var id = node.id;
		var parts = id.split("_");// {siteID}_content_{id}
		var type = parts[1];
		var content_id = parts[2];
		
		if(type == 'content' && GO.util.empty(content_id))
			return true;
		else
			return false;
	},
	
	isContentNode: function(node){
		var id = node.id;
		var parts = id.split("_");// {siteID}_content_{id}
		var type = parts[1];
		var content_id = parts[2];
		
		if(type == 'content' && !GO.util.empty(content_id))
			return true;
		else
			return false;
	},

	getRootNode: function(){
		return this.rootNode;
	},
	getExpandedNodes : function(){
		var expanded = new Array();
		this.getRootNode().cascade(function(n){
			if(n.expanded){
			expanded.push(n.attributes.id);
			}
		});
		
		return expanded;
	},
	rebuildTree: function(select){
		
		var rn = this.getRootNode();
		
		if(!rn){
			
			this.rootNode = new Ext.tree.AsyncTreeNode({
					id : this.siteId+'_content',
					draggable : false,
					site_id	:	this.siteId, 
					iconCls : 'go-icon-layout', 
					text : t("Content", "site"),
					expanded : true // Needs to be false, otherwise it will load records multiple times.
			});

			this.setRootNode(this.rootNode);
		}else
		{
			
			this.rootNode.id=this.siteId+'_content';
			this.rootNode.site_id=this.siteId;
			
			this.rootNode.reload();
		}
	
		var selectedNode = this.getSelectionModel().getSelectedNode();
//		this.getLoader().load(this.getRootNode());
		
		if(select)
			this.getSelectionModel().select(selectedNode); 
	}
});
	
	
