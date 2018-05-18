go.modules.community.files.FolderTreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	
	/**
	 * Defined rootNodes that usually will be skipped in processing below functions
	 */
	rootNodes : ['my-files','shared-with-me','bookmarks'],
	
	renderElements : function(n, a, targetNode, bulkRender){
		this.renderCustomIcon(n, a);
		go.modules.community.files.FolderTreeNodeUI.superclass.renderElements.call(this,n, a, targetNode, bulkRender);

		if(!n.getOwnerTree().folderSelectMode){
			this.applyNodeButton(this.anchor,n);
		}
	},
	/**
	 * Apply the dots menu after the treenode
	 * 
	 * @param {type} anchor
	 * @param {type} node
	 * @return {undefined}
	 */
	applyNodeButton : function(anchor,node){
		
		var entity = false;
		var menu = node.getOwnerTree().getContextMenu();
		
		//if(node.id !== 'my-files' && node.id !== 'shared-with-me' && node.id !== 'bookmarks'){
		if(!this.rootNodes.includes(node.id)){
			
			entity=node.attributes.entity;
			
			if(entity){
				var tnButton = new Ext.Button({
					iconCls:"ic-more-vert",
					cls:"tree-button",
					entity:entity,
					renderTo:anchor,
					menu: menu,
					listeners:{
						menushow : function(btn,menu){
							menu.setRecords([btn.entity]);
							menu.doLayout();
						}
					}
				});
				
				node.contextMenuButton = tnButton;
			}
		}		
	},
	
	/**
	 * Determine what kind of tree node we are creating and apply the correct icon class for it.
	 * 
	 * @param {type} node
	 * @param {type} a
	 * @return {undefined}
	 */
	renderCustomIcon : function(node, a){
		var entity = false;

		if(!this.rootNodes.includes(node.id)){
			entity=node.attributes.entity;
			if(entity){
				
				if(entity.id == 3){
				console.log(entity);
			}
				
				if(entity.bookmarked){
					a.iconCls = 'ic-folder-special';
				}

				if(entity.internalShared || entity.externalShared){
					a.iconCls = 'ic-folder-shared';
				}				
			}
		}		
	}
	
});