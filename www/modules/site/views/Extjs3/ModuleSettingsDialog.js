GO.site.ModuleSettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'siteModuleSettings',
			title:t("moduleSettings", "site"),
			formControllerUrl: 'site/content'
		});
		
		GO.site.ModuleSettingsDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {
		
		this.propertiesPanel = new Ext.Panel({
			title:t("globalProperties", "site"),			
			cls:'go-form-panel',
			layout:'form',
			items:[]
		});

		this.addPanel(this.propertiesPanel);
	}
});
