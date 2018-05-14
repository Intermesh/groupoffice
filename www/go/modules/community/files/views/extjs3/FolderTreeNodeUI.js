go.modules.community.files.FolderTreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	
	renderElements : function(n, a, targetNode, bulkRender){
		go.modules.community.files.FolderTreeNodeUI.superclass.renderElements.call(this,n, a, targetNode, bulkRender);
		
		if(!n.getOwnerTree().folderSelectMode){
			this.applyNodeButton(this.anchor,n);
		}
	},
	
	applyNodeButton : function(anchor,node){
		
		var entity = false;
		var menu = node.getOwnerTree().getContextMenu();
		if(node.id !== 'my-files' && node.id !== 'shared-with-me' && node.id !== 'bookmarks'){
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
	}
});