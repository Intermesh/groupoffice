/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MenuDialog.js 17133 2014-03-20 08:25:24Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.MenuDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	siteId : 0,
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'site_menu',
			title:GO.site.lang.menu,
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
			fieldLabel: GO.site.lang.menuLabel
		});
		
		this.menuSlugField = new Ext.form.TextField({
			name: 'menu_slug',
			anchor: '100%',
			maxLength: 255,
			allowBlank:false,
			fieldLabel: GO.site.lang.menuMenu_slug
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.site.lang.meta,		
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