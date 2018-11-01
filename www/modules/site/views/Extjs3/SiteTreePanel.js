GO.site.SiteTreePanel = function (config){
	config = config || {};
	
	config.loader =  new GO.base.tree.TreeLoader(
	{
		dataUrl:GO.url('site/site/tree'),
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
		ddGroup:'site-tree',
		enableDD:true,
		layout:'fit',
		split:true,
		autoScroll:true,
		width: 200,
		animate:true,
		rootVisible:false,
		containerScroll: true,
		selModel:new Ext.tree.DefaultSelectionModel()
	});
	
	GO.site.SiteTreePanel.superclass.constructor.call(this, config);

	// set the root node
	this.rootNode = new Ext.tree.AsyncTreeNode({
		draggable:false,
		id: 'root',
		iconCls : 'folder-default'
	});

	this.rootNode.on("beforeload", function(){
		//stop state saving when loading entire tree
		this.disableStateSave();
	}, this);

	this.setRootNode(this.rootNode);
	
	this.on('collapsenode', function(node)
	{		
		if(this.saveTreeState && node.childNodes.length)
			this.updateState();		
	},this);

	this.on('expandnode', function(node)
	{		
		if(node.id!="root" && this.saveTreeState && node.childNodes.length)
			this.updateState();
		
		
		//if root node is expanded then we are done loading the entire tree. After that we must start saving states
		if(node.id=="root"){			
			this.enableStateSave();
		}
	},this);

	this.on('contextmenu',this.onContextMenu, this);
	this.on('click',this.onTreeNodeClick, this);
	this.on('dblclick',this.onTreeNodeDblClick, this);
	this.on('nodedrop',this.onNodeDrop, this);
	this.on('movenode',this.onMoveNode, this);
	this.on('beforenodedrop',this.onBeforeNodeDrop, this);
}
	
Ext.extend(GO.site.SiteTreePanel, Ext.tree.TreePanel,{

	saveTreeState : false,
	loadingDone : false,
	
	getNodeObject : function (treeNode){
		
		var extractedNode = GO.site.extractTreeNode(treeNode);
		// Result = [siteId:siteId,	type:type, type_up:type.charAt(0).toUpperCase()+type.slice(1), modelId:modelId]
		switch(extractedNode['type']){
			case 'content':
				return new GO.site.treeNodes.ContentNode({
					treeNode:treeNode,
					extractedNode:extractedNode,
					treePanel:this
				});
				break;
			case 'menu':
				return new GO.site.treeNodes.MenuNode({
					treeNode:treeNode,
					extractedNode:extractedNode,
					treePanel:this
				});
				break;
			case 'menuitem':
				return new GO.site.treeNodes.MenuItemNode({
					treeNode:treeNode,
					extractedNode:extractedNode,
					treePanel:this
				});
				break;
			case 'site':
				return new GO.site.treeNodes.SiteNode({
					treeNode:treeNode,
					extractedNode:extractedNode,
					treePanel:this
				});
				break;
		}
		
	},

	// When clicked on a treenode
	onTreeNodeClick: function(node,e){
		node.select();
		
		GO.site.currentSiteId=node.attributes.site_id;
		
		return this.getNodeObject(node).click(node,e);
	},
	
	// When dblclicked on a treenode
	onTreeNodeDblClick: function(node,e){
		node.select();
		return this.getNodeObject(node).dblclick(node,e);
	},
	
	// When calling contextmenu on a treenode
	onContextMenu: function(node,e){
		node.select();
		return this.getNodeObject(node).contextmenu(node,e);
	},
	
	// Before the node is dropped
	onBeforeNodeDrop: function(e){
		if(e.dropNode)	
			return this.getNodeObject(e.dropNode).beforeNodeDrop(e.dropNode,e);
	},
	
	// When the node is dropped
	onNodeDrop: function(e){
		if(e.dropNode)	
			return this.getNodeObject(e.dropNode).nodeDrop(e.dropNode,e);
	},
	
	// When the node is dropped
	onMoveNode: function(tree, node, oldParent, newParent, index){
		if(newParent)	
			return this.getNodeObject(newParent).moveNode(tree, node, oldParent, newParent, index);
	},

	isRootNode : function(extractedNode){
		if(extractedNode.modelId){
			return false;
		} else {
			return true;
		}
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
					
	enableStateSave : function(){
		if(Ext.Ajax.isLoading(this.getLoader().transId)){
			this.enableStateSave.defer(100, this);
			this.loadingDone=false;
		}else
		{
			if(!this.loadingDone){
				this.loadingDone=true;
				this.enableStateSave.defer(100, this);
			}else{
				this.saveTreeState=true;
			}
		}
	},
	
	disableStateSave : function(){
		this.loadingDone=false;
		this.saveTreeState=false;
	},
	
	updateState : function(){
		GO.request({
			url:"site/site/saveTreeState",
			params:{
				expandedNodes:Ext.encode(this.getExpandedNodes())
			}
		});
	}					
});
