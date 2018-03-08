/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: RequirePasswordResetDialog.js 21065 2017-04-12 13:59:29Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 */
 
GO.dialog.RequirePasswordResetDialog = Ext.extend(GO.dialog.TabbedFormDialog , {

	initComponent : function(){
		
		Ext.apply(this, {
			enableApplyButton:false,
			enableOkButton:true,
			goDialogId:'go-require-password-reset-dialog',
			title:GO.lang.passwordExpired,
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
			fieldLabel: GO.lang.strCurrentPassword,
			name: 'current_password',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.newPasswordField = new Ext.form.TextField({
			fieldLabel: GO.lang.strNewPassword,
			name: 'password',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.confirmPasswordField = new Ext.form.TextField({
			fieldLabel: GO.lang.strConfirmPassword,
			name: 'confirm',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.passwordPanel = new Ext.Panel({
			title:GO.lang['passwordExpired'],			
			labelWidth: 120,
			waitMsgTarget:true,        
			bodyStyle:'padding:0px 0px 0px 0px',
			cls:'go-form-panel',
			layout:'form',
			items:[
				new GO.form.HtmlComponent({
					html: GO.lang.passwordNeedsChangeText+'<br/><br/>'
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