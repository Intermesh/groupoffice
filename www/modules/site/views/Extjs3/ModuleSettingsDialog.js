GO.site.ModuleSettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'siteModuleSettings',
			title:t("Settings"),
			formControllerUrl: 'site/content'
		});
		
		GO.site.ModuleSettingsDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {
		
		this.propertiesPanel = new Ext.Panel({
			title:t("Global properties", "site"),
			cls:'go-form-panel',
			layout:'form',
			items:[]
		});

		this.addPanel(this.propertiesPanel);
	}
});
