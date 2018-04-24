go.modules.community.files.FolderTree = Ext.extend(Ext.tree.TreePanel, {

	animate: true,
	enableDD: false,
	loader: new go.tree.TreeLoader({
		baseAttrs:{
			iconCls:'ic-folder'
		},
		entityStore: go.Stores.get("Node"),
		getParams : function(node) {

			var filter = {
				isDirectory:true
			};
			
			if(node.id !== 'my-files' && node.id !== 'shared-with-me' && node.id !== 'bookmarks'){
				filter.parentId=node.attributes.entity.id;
			}
			
			return {
				filter:filter
			};
		}
	}), // Note: no dataurl, register a TreeLoader to make use of createNode()
	lines: true,
	selModel: new Ext.tree.MultiSelectionModel(),
	containerScroll: false,

	rootVisible: false,

	rootNodes: [],

	initComponent: function () {
		
		// Add my files
		this.rootNodes.push({
			text: 'My files',
			iconCls:'ic-home',
			id:'my-files',
			params: {
				filter: {
					isHome: true
				}
			}
		});
		
		// Add shared with me
		this.rootNodes.push({
			text: 'Shared with me',
			iconCls:'ic-group',
			id:'shared-with-me',
			params: {
				filter: {
					isHome: false
				}
			}
		});
		
		// Add bookmarks
		this.rootNodes.push({
			text: 'Bookmarks',
			iconCls:'ic-bookmark',
			id:'bookmarks',
			params: {
				filter: {
					bookmarked: true
				}
			}
		});
		
		var root = new Ext.tree.TreeNode({
			expanded: true,
			text: 'ROOT',
			iconCls : 'ic-bookmark',
			draggable: false,
			children: this.rootNodes
		});

		go.modules.community.files.FolderTree.superclass.initComponent.call(this);

		this.setRootNode(root);
		this.getLoader().load(root);
	}

});

// Custom treeloader with go.tree.treeloader(entity:....);