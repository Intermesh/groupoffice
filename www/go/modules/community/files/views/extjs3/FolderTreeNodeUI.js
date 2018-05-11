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
						}
					}
				});
			}
		}

		
			
//			menu:new Ext.menu.Menu({
//				items: [
//					{
//						itemId: "copy",
//						iconCls: 'ic-content-copy',
//						text: t("Make copy"),
//						handler: function() {
//							if(entity){
//								console.log(t("Make copy"));
//							}
//						},
//						scope: this						
//					},{
//						itemId:"move",
//						iconCls: 'ic-forward',
//						text: t("Move to..."),
//						handler: function() {
//							if(entity){
//								var moveDialog = new go.modules.community.files.MoveDialog();
//								moveDialog.setTitle(t("Move")+ " " +entity.name);
//								moveDialog.load(entity.id).show();
//							}
//						},
//						scope: this						
//					},{
//						itemId:"search",
//						iconCls: 'ic-search',
//						text: t("Search in this folder"),
//						handler: function() {
//							if(entity){
//								console.log(t("Search in this folder"));
//							}
//						},
//						scope: this						
//					},{
//						itemId:"rename",
//						iconCls: 'ic-border-color',
//						text: t("Rename..."),
//						handler: function() {
//							if(entity){
//								var nodeDialog = new go.modules.community.files.NodeDialog();
//								nodeDialog.setTitle(t("Rename")+ " " +entity.name);
//								nodeDialog.load(entity.id).show();
//							}
//						},
//						scope: this						
//					},{
//						itemId:"delete",
//						iconCls: 'ic-delete',
//						text: t("Delete"),
//						handler: function() {
//							if(entity){
//								go.Stores.get("Node").set({destroy: [entity.id]}, function (options, success, response) {
//									if (response.destroyed) {
//										// success
//									}
//								}, this);
//							}
//						},
//						scope: this						
//					},'-',{
//						itemId:"share",
//						iconCls: 'ic-person-add',
//						text: t("Share..."),
//						handler: function() {
//							if(entity){
//								var shareDialog = new go.modules.community.files.ShareDialog();
//								shareDialog.setTitle(t("Share")+ " " +entity.name);
//								shareDialog.load(entity.id).show();
//							}
//						},
//						scope: this						
//					},'-',{
//						itemId:"bookmark",
//						iconCls: 'ic-bookmark',
//						text: t("Bookmark"),
//						handler: function() {
//							if(entity){
//								go.modules.community.files.bookmark([entity]);
//							}
//						},
//						scope: this						
//					}
//				]
//			})
		
	}
});