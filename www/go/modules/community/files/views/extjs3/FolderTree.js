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

	listeners: {
		
		'click' : function(node,e){
			//TODO: OWN LINKUI ELEMENT FOR TREENODE SO IT CAN BE STYLED WITH BUTTONS
			console.log(node,e);
			
			var t = e.getTarget();
			if (t.className = 'somecls'){
				// do something
			}
		},
		
		'mouseover': function(node,event){
			
			console.log(node);
//			node.setCls('WAUWIE');
			
		},
		'mouseout': function(node,event){
			
			console.log(node);
//			node.setCls('WAUWIE');
			
		},
		scope:this
	},

	initComponent: function () {
		
		// Add mouseover event to treenodes (https://www.sencha.com/forum/showthread.php?23479-TreeNode-mouseover-event)
		var NodeMouseoverPlugin = Ext.extend(Object, {
			init: function (tree) {
				if (!tree.rendered) {
					tree.on('render', function () {
						this.init(tree);
					}, this);
					return;
				}
				this.tree = tree;
				tree.body.on('mouseover', this.onTreeMouseover, this, {delegate: 'a.x-tree-node-anchor'});
				tree.body.on('mouseout', this.onTreeMouseout, this, {delegate: 'a.x-tree-node-anchor'});
			},

			onTreeMouseover: function (e, t) {

				var nodeEl = Ext.fly(t).up('div.x-tree-node-el');
				if (nodeEl) {
					var nodeId = nodeEl.getAttributeNS('ext', 'tree-node-id');
					if (nodeId) {
						this.tree.fireEvent('mouseover', this.tree.getNodeById(nodeId), e);
					}
				}
			},

			onTreeMouseout: function (e, t) {
				this.tree.fireEvent('mouseout', this.tree, e);
			}
		});
		
		this.plugins = new NodeMouseoverPlugin();
		
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