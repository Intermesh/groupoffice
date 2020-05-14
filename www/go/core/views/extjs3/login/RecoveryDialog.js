/* global go, Ext */

go.login.RecoveryDialog = Ext.extend(go.Window, {

	closable: false,
	resizable: false,
	draggable: false,
	width: 400,

	initComponent: function() {
		this.title = t('Reset password');
		this.items = [this.formPanel = new Ext.form.FormPanel({
			items:[{
				xtype:'fieldset',
				labelWidth: dp(200),
				items:[this.recoveryText = new Ext.BoxComponent({
						html: t("Loading"),
						cls: 'login-text-comp'
					}),
					this.displayField = new Ext.form.DisplayField({
						fieldLabel: t('Username')
					}),
					this.hashField = new Ext.form.Hidden({
						name:'hash'
					}),
					this.passwordField = new Ext.form.TextField({
						xtype:'textfield',
						inputType: 'password',
						name:'password',
						anchor: '0',
						fieldLabel: t('New password'),
						allowBlank: false
					})
					,
					this.confirmPasswordField = new Ext.form.TextField({
						xtype:'textfield',
						inputType: 'password',
						name:'confirmPassword',
						anchor: '0',
						fieldLabel: t('Confirm password'),
						allowBlank: false
					})
					]
				}]
		})];
	
		this.buttons = [{
			text: t('Change'),
			handler:function() {
				
				if(this.passwordField.getValue() != this.confirmPasswordField.getValue()) {
					this.confirmPasswordField.markInvalid(t("The passwords didn't match"));
					return;
				}
				
				if(!this.passwordField.isValid() || !this.confirmPasswordField.isValid()) {
					return;
				}
				
				Ext.Ajax.request({
					url: go.AuthenticationManager.getAuthUrl(),
					jsonData: {
						recover: true,
						hash: this.hashField.getValue(),
						newPassword: this.passwordField.getValue()
					},
					callback: function(options, success, response){
						var result = Ext.decode(response.responseText);
						if(success && result.passwordChanged){ // Password has been change successfully
							go.Router.setPath("");
							
							if(this.redirectUrl) {
								document.location = this.redirectUrl;
							} else
							{
								
								this.close();
								GO.mainLayout.login();
								Ext.MessageBox.alert(t("Success"), t("Your password was changed successfully"));
							}
						} else
						{
							//mark validation errors
							for(name in result.validationErrors) {
								var field = this.formPanel.getForm().findField(name);
								if(field) {
									field.markInvalid(result.validationErrors[name].description);
								}
							}

							Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " + response.statusText);
						}
					},
					scope: this	
				});
			},
			scope:this
		}];

		go.login.RecoveryDialog.superclass.initComponent.call(this);
	},
	
	show : function(hash, redirectUrl) {
		go.login.RecoveryDialog.superclass.show.call(this);
		
		this.redirectUrl = redirectUrl;
		
		Ext.Ajax.request({
			url: go.AuthenticationManager.getAuthUrl(),
			jsonData: {
				recover: true,
				hash: hash
			},
			callback: function(options, success, response){
				var result = Ext.decode(response.responseText);
				if(success && result.username){
					this.displayField.setValue(result.username);
					this.hashField.setValue(hash);
					this.recoveryText.el.update(result.displayName);
				} else {
					this.close();
					Ext.MessageBox.alert(t("Error"), t("This password recovery link is invalid"));
				}
			},
			scope: this	
		});

	}
});
