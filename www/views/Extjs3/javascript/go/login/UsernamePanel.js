go.login.UsernamePanel = Ext.extend(go.login.BaseLoginPanel, {
	
	initComponent: function() {
		
//		this.usernameText = new GO.form.HtmlComponent({
//			html: t("Login required"),
//			cls: 'login-text-comp'
//		});
		
		this.usernameField = new Ext.form.TextField({
			itemId: 'username',
			fieldLabel: t("Username"),
			name: 'username',
			allowBlank:false,
			anchor:'100%'
		});
		
		this.passwordField = new Ext.form.TextField({
			itemId: 'password',
			fieldLabel: t("Password"),
			name: 'password',
			inputType: 'password',
			allowBlank:false,
			anchor:'100%'
		});
				
		Ext.apply(this,{
			id: 'usernameCheck',
			items: [
				this.usernameField,
				this.passwordField,
				{
					xtype: "xcheckbox",
					name: "remind",
					value: false,
					boxLabel: "Remember my login on this computer until I press logout",
					listeners: {
						check : function(checked) {
							go.AuthenticationManager.rememberLogin = checked;
						}
					}
				}
			]
		});
	
		go.login.UsernamePanel.superclass.initComponent.call(this);
	},
	
	setErrors: function(errors){

		for (var key in errors) {
			if(key == "password") {
				switch(parseInt(errors[key].code)){
					case 1:
						this.passwordField.markInvalid(t('Password is required'));
						break;
					case 5:
						this.passwordField.markInvalid(t('Not found'));
						break;
					case 10:
					default:
						this.passwordField.markInvalid(t('Invalid password'));
						break;
				}
			} else
			{				
				this.usernameField.markInvalid(t("Bad username or password"));				
			}
		}
	},
	
	submit: function(loginDialog) {
		
		loginDialog.getEl().mask();
		
		go.AuthenticationManager.getAvailableMethods(this.usernameField.getValue(), this.passwordField.getValue(),function(authMan, success, result){
			loginDialog.getEl().unmask();
			if(success){				
				loginDialog.next();
			} else {
				this.setErrors(result.errors);
			}
		},this);
	}
});
