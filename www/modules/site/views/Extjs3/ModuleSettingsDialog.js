GO.site.ModuleSettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'siteModuleSettings',
			title:GO.site.lang.moduleSettings,
			formControllerUrl: 'site/content'
		});
		
		GO.site.ModuleSettingsDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {
		
		this.propertiesPanel = new Ext.Panel({
			title:GO.site.lang.globalProperties,			
			cls:'go-form-panel',
			layout:'form',
			items:[]
		});

		this.addPanel(this.propertiesPanel);
	}
});