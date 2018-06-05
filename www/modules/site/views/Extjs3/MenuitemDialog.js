/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: MenuitemDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.site.MenuitemDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	menuId : 0,
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'site_menuitem',
			title:t("Menu item", "site"),
			formControllerUrl: 'site/menuItem',
			updateAction : 'update',
			createAction	: 'create',
			height:250,
			width:400
		});
		
		GO.site.MenuitemDialog.superclass.initComponent.call(this);		
	},
	
	buildForm : function () {
		
		this.labelField = new Ext.form.TextField({
			name: 'label',
			anchor: '100%',
			maxLength: 255,
			allowBlank:false,
			fieldLabel: t("Label", "site")
		});
		
		this.urlField = new Ext.form.TextField({
			name: 'url',
			anchor: '100%',
			maxLength: 255,
			allowBlank:false,
			fieldLabel: t("Url", "site")
		});
		
		this.parentSelect = new GO.form.ComboBox({
			fieldLabel: t("Parent", "site"),
			hiddenName:'parent_id',
			anchor:'100%',
			store: GO.site.availableMenuParentsStore,
			valueField:'id',
			displayField:'label',
			mode: 'remote',
			triggerAction: 'all',
			allowBlank: true
		});
		
		this.contentSelect = new GO.form.ComboBox({
			fieldLabel: t("Content", "site"),
			hiddenName:'content_id',
			anchor:'100%',
			store: GO.site.availableMenuContentsStore,
			valueField:'id',
			displayField:'title',
			mode: 'remote',
			triggerAction: 'all',
			allowBlank: true
		});
		
		this.targetSelect = new GO.form.ComboBoxReset({
			fieldLabel: t("Target", "site"),
			hiddenName:'target',
			anchor:'100%',
			store: GO.site.linkTargetStore,
			valueField:'value',
			displayField:'label',
			mode: 'local',
			triggerAction: 'all',
			allowBlank: true
		});
		
		this.displayChildrenCbx = new Ext.ux.form.XCheckbox({
			hideLabel: false,
			boxLabel: t("Display children", "site"),
			name: 'display_children',
			value: false
		});
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Meta", "site"),		
			cls:'go-form-panel',
			layout:'form',
			items:[
				this.labelField,
				this.urlField,
				this.displayChildrenCbx,
				this.parentSelect,
				this.contentSelect,
				this.targetSelect
			]
		});
		
		this.addPanel(this.propertiesPanel);
	},
		
	setMenuId : function(menuId){
		this.menuId = menuId;
		this.addBaseParam('menu_id', menuId);
	},
	
	setParentId : function(parentId){
		this.parentSelect.setValue(parentId);
	},
	
	afterShowAndLoad : function(remoteModelId, config){
		GO.site.MenuitemDialog.superclass.afterShowAndLoad.call(this,remoteModelId, config);
		
		this.parentSelect.store.baseParams.menu_id = this.menuId;
		this.contentSelect.store.baseParams.menu_id = this.menuId;
		
		if(remoteModelId)
			this.parentSelect.store.baseParams.id = remoteModelId;
		
		this.parentSelect.store.load();
	}
});
