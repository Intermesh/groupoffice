/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: RequirePasswordResetDialog.js 22112 2018-01-12 07:59:41Z mschering $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.dialog.RequirePasswordResetDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			enableApplyButton:false,
			enableOkButton:true,
			goDialogId:'go-require-password-reset-dialog',
			title:t("Your password is expired."),
			formControllerUrl: 'core/auth',
			submitAction: 'resetExpiredPassword',
			loadOnNewModel:false,
			width: 430,
			height:230,
			keys: [{
				key: Ext.EventObject.ENTER,
				fn: this.submitForm,
				scope:this
		}]
		});
		
		GO.dialog.RequirePasswordResetDialog.superclass.initComponent.call(this);	
	},
	
	buildForm : function () {
		
		this.usernameField = new Ext.form.Hidden({
			name: 'username'
		});
		
		this.currentPasswordField = new Ext.form.TextField({
			fieldLabel: t("Current Password"),
			name: 'current_password',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.newPasswordField = new Ext.form.TextField({
			fieldLabel: t("New Password"),
			name: 'password',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.confirmPasswordField = new Ext.form.TextField({
			fieldLabel: t("Confirm password"),
			name: 'confirm',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.passwordPanel = new Ext.Panel({
			title:t("Your password is expired."),			
			labelWidth: 120,
			waitMsgTarget:true,        
			bodyStyle:'padding:0px 0px 0px 0px',
			cls:'go-form-panel',
			layout:'form',
			items:[
				new GO.form.HtmlComponent({
					html: t("Please create a new password to continue working in Group-Office.")+'<br/><br/>'
				}),
				this.usernameField,
				this.currentPasswordField,
				this.newPasswordField,
				this.confirmPasswordField
      ]				
		});

		this.addPanel(this.passwordPanel);
	},
	
	show : function(username){
		this.usernameField.setValue(username);
		GO.dialog.RequirePasswordResetDialog.superclass.show.call(this);	
	},
	
	// Override existing function because it is not needed here
	refreshActiveDisplayPanels : function(){
		
	}
});
