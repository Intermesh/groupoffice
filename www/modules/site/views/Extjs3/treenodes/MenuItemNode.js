/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MenuItemNode.js 16600 2014-01-10 13:48:07Z wsmits $
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
						text: GO.site.lang.properties,
						cls: 'x-btn-text-icon',
						scope:this,
						handler: function(){
							this.openMenuItemDialog(false);
						}
					}),
					new Ext.menu.Item({
						iconCls: 'btn-add',
						text: GO.site.lang.addMenuItem,
						cls: 'x-btn-text-icon',
						handler:function(){
							this.openMenuItemDialog(true);
						},
						scope:this
					}),
					new Ext.menu.Item({
						iconCls: 'btn-delete',
						text: GO.site.lang['delete'],
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
			
			this.errorDialog.show(GO.site.lang.deleteMenuHasChildren, GO.site.lang.deleteMenu);
			
		} else {
			
			Ext.MessageBox.confirm(GO.site.lang.deleteMenu, GO.site.lang.deleteMenuConfirm, function(btn){
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