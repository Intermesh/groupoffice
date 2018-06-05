/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MenuDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.MenuDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	siteId : 0,
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'site_menu',
			title:t("Menu", "site"),
			formControllerUrl: 'site/menu',
			updateAction : 'update',
			createAction	: 'create',
			height:150,
			width:300
		});
		
		GO.site.MenuDialog.superclass.initComponent.call(this);		
	},
	
	buildForm : function () {
		
		this.labelField = new Ext.form.TextField({
			name: 'label',
			anchor: '100%',
			maxLength: 255,
			allowBlank:false,
			fieldLabel: t("Label", "site")
		});
		
		this.menuSlugField = new Ext.form.TextField({
			name: 'menu_slug',
			anchor: '100%',
			maxLength: 255,
			allowBlank:false,
			fieldLabel: t("Slug", "site")
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Meta", "site"),		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.labelField,
				this.menuSlugField
			]
		});
		
		this.addPanel(this.propertiesPanel);
	},
		
	setSiteId : function(siteId){
		this.siteId = siteId;
		this.addBaseParam('site_id', siteId);
		
	}
});
