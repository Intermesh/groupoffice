go.modules.community.files.FolderTree = Ext.extend(Ext.tree.TreePanel, {
	animate: true,
	enableDD: false,
	browser:null,
	loader: new go.tree.TreeLoader({
		baseAttrs:{
			iconCls:'ic-folder',
			uiProvider:go.modules.community.files.FolderTreeNodeUI
		},
		entityStore: go.Stores.get("Node"),
		getParams : function(node) {
	
			var filter = {
				isDirectory:true
			};

			if(node.attributes.entity){ // Root nodes don't have an entity set
				filter.parentId=node.attributes.entityId;
			}
			
			return {
				filter:filter
			};
		}
	}),
	lines: true,
	containerScroll: false,

	rootVisible: false,

	initComponent: function () {

		var root = new Ext.tree.TreeNode({
			expanded: true,
			text: 'ROOT',
			draggable: false,
			children: this.browser.rootNodes
		});

		go.modules.community.files.FolderTree.superclass.initComponent.call(this);

		this.on('click',function(node,e){
			this.openFolder(node,e);			
		},this);
	
		this.setRootNode(root);
		this.getLoader().load(root);		
	},
	
	openFolder: function(node,e){
		this.browser.goto(this.getPath(node));
	},
	
	getPath : function(node){
		var p = node.parentNode;
    var b = [node.attributes['entityId']];
		while(p){
			if(p.attributes['entityId']){
				b.unshift(p.attributes['entityId']);
			}
			p = p.parentNode;
		}
		return b;
	}
});