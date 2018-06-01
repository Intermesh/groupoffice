/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: NewMenuButton.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 GO.NewMenuButton = Ext.extend(Ext.Button, {
	panel : false,
	/**
		* Set different show config objects per menu item.
		*/
	showConfigs : {},
	initComponent : function(){
		
		this.menu = new Ext.menu.Menu({				
				items:GO.newMenuItems,
				panel:this.panel,
				showConfigs : this.showConfigs
			});
		this.tooltip=t("New");
		this.iconCls='btn-add';			
		this.disabled=true;
		this.hidden=GO.newMenuItems.length==0;
			
		GO.NewMenuButton.superclass.initComponent.call(this);		
	},
	
	setLinkConfig : function(config){
		this.menu.linkConfig=config;		
		this.menu.linkConfig.modelNameAndId=config.model_name+':'+config.model_id;
		
		if(!this.menu.linkConfig.scope)
		{
			this.menu.linkConfig.scope=this;
		}
		
		if(this.menu.linkConfig.callback)
		{
			this.menu.linkConfig.callback=this.menu.linkConfig.callback.createDelegate(this.menu.linkConfig.scope);
		}
		
		this.menu.link_config=this.menu.linkConfig;
		
		this.setDisabled(GO.util.empty(config.model_id));
	}	
	
});


 GO.NewMenuItem = Ext.extend(Ext.menu.Item, {
	initComponent : function(){

		this.menu = new Ext.menu.Menu({
				items:GO.newMenuItems
			});
		this.text=t("New");
		this.iconCls='btn-add';
		this.disabled=true;
		this.hidden=GO.newMenuItems.length==0;

		GO.NewMenuButton.superclass.initComponent.call(this);
	},

	setLinkConfig : function(config){
		this.menu.linkConfig=config;
		this.menu.linkConfig.modelNameAndId=config.model_name+':'+config.model_id;

		if(!this.menu.linkConfig.scope)
		{
			this.menu.linkConfig.scope=this;
		}

		if(this.menu.linkConfig.callback)
		{
			this.menu.linkConfig.callback=this.menu.linkConfig.callback.createDelegate(this.menu.linkConfig.scope);
		}
		
		this.menu.link_config=this.menu.linkConfig;

		this.setDisabled(GO.util.empty(config.model_id));
	}

});

//
//GO.mainLayout.onReady(function(){
//	GO.newMenuItems.unshift({
//		text: t("Link"),
//		iconCls: 'has-links',
//		handler:function(item, e)
//		{
//			if(!this.linksDialog)
//			{
//				this.linksDialog = new GO.dialog.LinksDialog();
//				this.linksDialog.on('link', function()
//				{
//					if(item.parentMenu.panel)
//						item.parentMenu.panel.reload();
//				});
//			}
//
//			this.linksDialog.setSingleLink(item.parentMenu.linkConfig.model_id, item.parentMenu.linkConfig.model_name);
//			this.linksDialog.show();
//		}
//	});
//});
