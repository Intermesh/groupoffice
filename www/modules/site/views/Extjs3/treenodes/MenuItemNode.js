/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MenuItemNode.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.treeNodes.MenuItemNode = Ext.extend(GO.site.treeNodes.AbstractNode , {
	
	contextmenu : function(node, e){
		// Doe iets
		if(!this.contextMenu){
			this.contextMenu = new Ext.menu.Menu({
				items : [
					new Ext.menu.Item({
						iconCls: 'btn-settings',
						text: t("Properties", "site"),
						cls: 'x-btn-text-icon',
						scope:this,
						handler: function(){
							this.openMenuItemDialog(false);
						}
					}),
					new Ext.menu.Item({
						iconCls: 'btn-add',
						text: t("New item", "site"),
						cls: 'x-btn-text-icon',
						handler:function(){
							this.openMenuItemDialog(true);
						},
						scope:this
					}),
					new Ext.menu.Item({
						iconCls: 'btn-delete',
						text: t("Delete", "site"),
						cls: 'x-btn-text-icon',
						scope:this,
						handler: function(){
							this.deleteMenuItem();
						}
					})
				]
			});
		}
			
		this.contextMenu.showAt(e.xy);
	},
	
	dblclick: function(node, e){
		// Doe iets
	},
	click: function(node, e){
		// Doe iets
	},
		
	openMenuItemDialog : function(newItem){
		if(!GO.site.menuitemDialog){
			GO.site.menuitemDialog = new GO.site.MenuitemDialog();
			GO.site.menuitemDialog.on('hide',function(){
				GO.mainLayout.getModulePanel('site').rebuildTree();
			},this);
		}

		GO.site.menuitemDialog.setMenuId(this.treeNode.attributes.menu_id);
		
		if(newItem){
			GO.site.menuitemDialog.setParentId(this.extractedNode.modelId);
			GO.site.menuitemDialog.show();
		}else{
			GO.site.menuitemDialog.show(this.extractedNode.modelId);
		}
	},
	
	deleteMenuItem : function(){

		if(this.treeNode.attributes.hasChildren){
			
			if(!this.errorDialog)
				this.errorDialog = new GO.ErrorDialog();
			
			this.errorDialog.show(t("The selected menu-item has children and cannot be deleted.", "site"), t("Delete menu item", "site"));
			
		} else {
			
			Ext.MessageBox.confirm(t("Delete menu item", "site"), t("Do you really want to delete this menu item?", "site"), function(btn){
				if(btn == 'yes'){
					GO.request({
						url: 'site/menuItem/delete',
						params: {
							id: this.extractedNode.modelId,
							menu_id: this.treeNode.attributes.menu_id
						},
						success: function(){
							GO.mainLayout.getModulePanel('site').rebuildTree();
						},
						failure: function(){},
						scope: this
					});
				}
			},this);
			
		}
	}
});
