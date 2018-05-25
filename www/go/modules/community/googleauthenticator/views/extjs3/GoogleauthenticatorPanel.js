go.googleauthenticator.GoogleauthenticatorPanel = Ext.extend(go.login.BaseLoginPanel, {
	
	initComponent: function() {
		
		this.secretText = new GO.form.HtmlComponent({
			html: t("Get the code from the google authenticator app on your mobile device and fill it in below")+'.',
			cls: 'login-text-comp'
		});
		
		this.secretField = new Ext.form.TextField({
			itemId: 'googleauthenticator_code',
			fieldLabel: t('Code','googleauthenticator'),
			name: 'googleauthenticator_code',
			allowBlank:false,
			anchor:'100%'
		});
		
		Ext.apply(this,{
			
			items: [
				this.secretText,
				this.secretField
			]
		});
	
		go.googleauthenticator.GoogleauthenticatorPanel.superclass.initComponent.call(this);
	},
	setErrors: function(errors){
		for (var key in errors) {

			switch(parseInt(errors[key].code)){
				case 1:
					this.secretField.markInvalid(t('Code is required'));
					break;
				case 5:
					this.secretField.markInvalid(t('Not found'));
					break;
				case 10:
					this.secretField.markInvalid(t('Invalid code'));
					break;
			}
		}
	}
});

go.AuthenticationManager.register('googleauthenticator', new go.googleauthenticator.GoogleauthenticatorPanel());
