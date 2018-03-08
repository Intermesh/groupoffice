GO.cron.SettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'cronsettings',
			title:GO.cron.lang.cronSettings,
			formControllerUrl: 'core/cron',
			submitAction : 'submitSettings',
			loadAction : 'loadSettings',
			height:350,
			width:500
		});
		
		GO.cron.SettingsDialog.superclass.initComponent.call(this);	
	},
	  
	buildForm : function () {
		this.periodGrid = new GO.cron.PeriodGrid({
			layout:'fit',
			border:true
		});	
			
		this.periodPanel = new Ext.Panel({
			title:GO.cron.lang.runUpcoming,			
			layout:'fit',
			items:[
				this.periodGrid
      ]				
		});
			
			
		this.propertiesPanel = new Ext.Panel({
			title:GO.lang['strProperties'],			
			cls:'go-form-panel',
			layout:'form',
			items:[
				//this.defaultCanConnectCheckBox
      ]				
		});

		this.addPanel(this.propertiesPanel);
		this.addPanel(this.periodPanel);
	}
});