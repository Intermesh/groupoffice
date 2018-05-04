go.modules.community.files.FolderTree = Ext.extend(Ext.tree.TreePanel, {
	animate: true,
	enableDD:true,
	dropConfig: {
		appendOnly:true
	},
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

		go.modules.community.files.FolderTree.superclass.initComponent.call(this);

		this.on('click',function(node,e){
			this.browser.goto(this.getPath(node));
		},this);
	
		this.on('nodedrop',function(dropEvent){
			this.moveFolder(dropEvent.dropNode,dropEvent.target);
		},this);
		
		this.browser.on('pathchanged', function(){
			this.openPath(this.browser.getPath(true));
		},this);
		
		var root = new Ext.tree.TreeNode({
			expanded: true,
			text: 'ROOT',
			entityId:'ROOT', // Needed so it can be handled exactly as other nodes
			draggable: false,
			children: this.browser.rootNodes
		});
		
		this.setRootNode(root);
		this.getLoader().load(root);
	},
	
	/**
	 * 
	 * @param Ext.tree.AsyncTreeNode nodeToMove
	 * @param Ext.tree.AsyncTreeNode targetNode
	 * @return {undefined}
	 */
	moveFolder : function(nodeToMove,targetNode){
		
		var nodeToUpdateId = nodeToMove.attributes.entityId;
		
		var params = {}, me=this, newParentId=targetNode.attributes.entityId;
		
		// Workaround for myfiles
		if(newParentId === 'my-files'){
			newParentId = go.User.storage.rootFolderId;
		}
		
		params.update = {};
		params.update[nodeToUpdateId] = {
			parentId:newParentId
		};

		go.Stores.get("Node").set(params, function (options, success, response) {
			
			var saved = response.updated || {};
			if (saved[nodeToUpdateId]) {				
				this.fireEvent("save", this, params.update[nodeToUpdateId]);
			} else {
				//something went wrong
				var notSaved = response.notUpdated || {};
				if (!notSaved[nodeToUpdateId]) {
					notSaved[nodeToUpdateId] = {type: "unknown"};
				}

				switch (notSaved[nodeToUpdateId].type) {
					case "forbidden":
						Ext.MessageBox.alert(t("Access denied"), t("Sorry, you don't have permissions to update this item"));
						break;

					default:
						Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
						break;
				}
			}

		});
	},
	
	/**
	 * Get the path of the given node.
	 * Walks from the node back to the top (rootNode)
	 * 
	 * @param {type} node
	 * @return {Array}
	 */
	getPath : function(node){
		var p = node.parentNode;
    var b = [node.attributes['entityId']];
		while(p){
			if(p.attributes['entityId'] && p.attributes['entityId'] != 'ROOT'){
				b.unshift(p.attributes['entityId']);
			}
			p = p.parentNode;
		}
		return b;
	},
	
	/**
	 * Open child nodes in the tree based on the given path
	 * 
	 * @param array path
	 */
	openPath : function(path){
		var treePath = this.pathSeparator+'ROOT'+this.pathSeparator+path.join(this.pathSeparator);
		this.expandPath(treePath,'entityId');
	}
});