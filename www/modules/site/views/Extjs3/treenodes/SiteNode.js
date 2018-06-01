/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: SiteNode.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.treeNodes.SiteNode = Ext.extend(GO.site.treeNodes.AbstractNode , {
	
	contextmenu : function(node, e){
		if(!this.contextMenu){
				this.contextMenu = new Ext.menu.Menu({
					items : [
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
							iconCls: 'btn-settings',
							text: t("Properties", "site"),
							cls: 'x-btn-text-icon',
							scope:this,
							handler: function(){
								this.openSiteDialog();
							}
						})
					]
				});
			}
			
			this.contextMenu.showAt(e.xy);
	},
	
	dblclick: function(self, e){
		// Doe iets
	},
	
	click: function(self, e){
		// Doe iets
	},
	openSiteDialog : function(){
		if(!GO.site.siteDialog){
			GO.site.siteDialog = new GO.site.SiteDialog();
			GO.site.siteDialog.on('hide',function(){
				GO.mainLayout.getModulePanel('site').rebuildTree();
			},this);
		}

		GO.site.siteDialog.show(this.extractedNode.siteId);
	},
	viewExample : function(){
		window.open(GO.settings.config.host+'modules/site/index.php?site_id='+this.extractedNode.siteId);			
	}
});
