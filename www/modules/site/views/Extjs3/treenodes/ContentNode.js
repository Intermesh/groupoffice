/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: ContentNode.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.treeNodes.ContentNode = Ext.extend(GO.site.treeNodes.AbstractNode , {
	
	contextmenu : function(node, e){
		// Doe iets

		if(this.isRootNode(this.extractedNode)){
			
			if(!this.rootContextMenu){
				this.rootContextMenu = new Ext.menu.Menu({
					items : [
						new Ext.menu.Item({
							iconCls: 'btn-add',
							text: t("Add content", "site"),
							cls: 'x-btn-text-icon',
							handler:function(){
								// Create, only send the siteId because it need to be created in the root
								this.treePanel.contentPanel.create(this.extractedNode.siteId);
							},
							scope:this
						})
					]
				});
			}
			
			this.rootContextMenu.showAt(e.xy);
			
		} else {
			
			if(!this.contextMenu){
				this.contextMenu = new Ext.menu.Menu({
					items : [
						new Ext.menu.Item({
							iconCls: 'btn-settings',
							text: t("Advanced options", "site"),
							cls: 'x-btn-text-icon',
							scope:this,
							handler: function()
							{			
								this.treePanel.contentPanel.showContentDialog(this.extractedNode.modelId);
							}
						}),
						new Ext.menu.Item({
							iconCls: 'btn-view',
							text: t("View"),
							cls: 'x-btn-text-icon',
							handler:function(){
								this.viewExample();
							},
							scope:this
						}),
						new Ext.menu.Item({
							iconCls: 'btn-add',
							text: t("Add content", "site"),
							cls: 'x-btn-text-icon',
							handler:function(){
								// Load an empty contentPanel and set the parent id
								this.treePanel.contentPanel.create(this.extractedNode.siteId,this.extractedNode.modelId);
							},
							scope:this
						}),
						new Ext.menu.Item({
							iconCls: 'btn-delete',
							text: t("Delete content", "site"),
							cls: 'x-btn-text-icon',
							scope:this,
							handler: function()
							{
								this.deleteContent();
							}
						})
					]
				});
			}
			
			this.contextMenu.showAt(e.xy);
		}
		
	},
	
	dblclick: function(node, e){
		// Doe iets
	},
	
	click: function(node, e){
		// Doe iets
		if(!this.isRootNode(this.extractedNode))
			this.treePanel.contentPanel.load(this.extractedNode.modelId);
	},
	
	deleteContent : function() {
		
		if(this.treeNode.attributes.hasChildren){
			if(!this.errorDialog){
				this.errorDialog = new GO.ErrorDialog();
			}
			this.errorDialog.show(t("You cannot delete this item because it has subitems. Please delete the subitems first.", "site"), t("Delete content", "site"));
		} else {
			var contentId = this.extractedNode.modelId;

			Ext.MessageBox.confirm(t("Delete content", "site"), t("Are you sure that you want to delete this content item", "site"), function(btn){
				if(btn == 'yes'){
					GO.request({
						url: 'site/content/delete',
						params: {
							id: contentId
						},
						success: function(){
							GO.mainLayout.getModulePanel('site').rebuildTree();
						},
						failure: function(){

						},
						scope: this
					});
				}
			});
		}
	},
	viewExample : function(){
		window.open(GO.settings.config.host+'modules/site/index.php?site_id='+this.extractedNode.siteId+'&slug='+this.treeNode.attributes.slug);			
	},
	
	beforeNodeDrop: function(node, e){

		var target = GO.site.extractTreeNode(e.target);

		if(e.point !== "append"){
			target = GO.site.extractTreeNode(e.target.parentNode);
		}
				
		var content = GO.site.extractTreeNode(node);
		
		e.dropStatus = true;
		
		if(target.type === 'menuitem' || target.type === 'menu'){
			// TARGET CAN BE
			// Object {siteId: "1", type: "menu", type_up: "Menu", modelId: false} 
			// Object {siteId: "1", type: "menu", type_up: "Menu", modelId: "11"} 
			// Object {siteId: "1", type: "menuitem", type_up: "Menuitem", modelId: "30"}
			
			//Confirm tonen met daarin de vraag of je het content item zeker toe wilt voegen. 
			Ext.MessageBox.confirm(t("Create menu item from content", "site"), t("Are you sure that you want to create a menu item from this content item?", "site"), function(btn){
				if(btn == 'yes'){
					
					GO.request({
						method:"POST",
						url: 'site/menuItem/createFromContent',
						params: {
							target:Ext.encode(target),
							content:Ext.encode(content)
						},
						success: function(){
							e.target.reload();
						},
						failure: function(){
							//console.log('ERROR');
						},
						scope: this
					});
				}
			});
			return false;
		} else {
			return GO.site.treeNodes.ContentNode.superclass.nodeDrop.call(this, node, e);
		}
	}
});
