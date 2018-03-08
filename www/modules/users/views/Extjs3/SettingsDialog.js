/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: SettingsDialog.js 14816 2013-05-21 08:31:20Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

GO.users.SettingsDialog = Ext.extend(GO.dialog.TabbedFormDialog , {
	
	initComponent : function(){
		
		Ext.apply(this, {
			goDialogId:'usersettings',
			title: GO.lang['cmdSettings'],
			formControllerUrl: 'users/settings',
			height:360,
			width:500
//			,
//			helppage:'Z-push_admin_user_manual#Settings'
		});
		
		GO.users.SettingsDialog.superclass.initComponent.call(this);	
	},
	  
	buildForm : function () {
		
		this.registerEmailPanel = new GO.users.RegisterEmailPanel();
		this.selectSettingPanels = new GO.users.SelectSettingPanels();
	
		this.addPanel(this.registerEmailPanel);
		this.addPanel(this.selectSettingPanels);
	}
});