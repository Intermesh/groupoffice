/* global go, Ext */

go.login.RecoveryDialog = Ext.extend(go.Window, {

	closable: false,
	resizable: false,
	draggable: false,
	width: 400,

	initComponent: function() {
		this.title = t('Reset password');
		this.items = [{
			xtype:'container',
			items: [
				{xtype:'box',cls: "go-app-logo"},
				this.avatarComp = new Ext.BoxComponent({
					autoEl: 'img',
					cls: "login-avatar user-img",
					setImageUrl: function(url){
						// TODO: Enable this when url is OK
						//this.getEl().dom.src = url;
					},
					clearAvatar: function(){
						this.setImageUrl('');
						this.setVisible(false);
					}
				})
			]
		},this.formPanel = new Ext.form.FormPanel({
			items:[{
				xtype:'fieldset',
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
					url:BaseHref+'auth.php',
					jsonData: {
						recover: true,
						hash: this.hashField.getValue(),
						newPassword: this.passwordField.getValue()
					},
					callback: function(options, success, response){
						var result = Ext.decode(response.responseText);
						if(success && result.passwordChanged){ // Password has been change successfully
							
							if(this.redirectUrl) {
								document.location = this.redirectUrl;
							} else
							{
								this.close();
								GO.mainLayout.login();
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

							Ext.MessageBox.alert(t("Error"), t("Sorry, something went wrong. Please try again."));
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
			url:BaseHref+'auth.php',
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
					this.recoveryText.el.update(t('Cannot recover your password with the given link'));
					this.passwordField.setVisible(false);
					this.displayField.setVisible(false);
					this.getFooterToolbar().setDisabled(true);
				}
			},
			scope: this	
		});

	}
});
