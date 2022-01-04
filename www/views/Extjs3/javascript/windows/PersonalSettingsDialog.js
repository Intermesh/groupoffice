/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: PersonalSettingsDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
 
GO.PersonalSettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'settings',
			title:t("Settings"),
			formControllerUrl: 'settings',
			width:900,
			fileUpload : true,
			height:550,
			maximizable:true,
			enableApplyButton:false
		});
		
		GO.PersonalSettingsDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function(){
		var panels =GO.moduleManager.getAllSettingsPanels();
		
		for(var i=0;i<panels.length;i++)
			this.addPanel(panels[i]);
	},
	
	afterLoad : function(remoteModelId, config, action){
		for(var i=0;i<this._tabPanel.items.getCount();i++)
		{
			var panel = this._tabPanel.items.itemAt(i);
			if(panel.onLoadSettings)
			{
				var func = panel.onLoadSettings.createDelegate(panel, [action]);
				func.call();							 
			}
		}			
	},

	beforeSubmit : function(){
		for(var i=0;i<this._tabPanel.items.getCount();i++)
		{
			var panel = this._tabPanel.items.itemAt(i);
			if(panel.onBeforeSaveSettings)
			{
				var func = panel.onBeforeSaveSettings.createDelegate(panel, [this]);
				var result = func.call();
				if(!result)
				{
					this._tabPanel.setActiveTab(panel);
					return false;
				}
			}
		}
	},
	
	show : function (remoteModelId, config) {
		
		remoteModelId = GO.settings.user_id;
		GO.PersonalSettingsDialog.superclass.show.call(this, remoteModelId, config);	
	},
	afterSubmit : function(action) {	
		// Reload Groupoffice to use the new settings
		go.reload();
	}
});
