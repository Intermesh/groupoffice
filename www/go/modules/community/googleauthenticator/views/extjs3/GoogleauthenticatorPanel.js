go.googleauthenticator.GoogleauthenticatorPanel = Ext.extend(go.login.BaseLoginPanel, {

	initComponent: function () {

		this.secretText = new GO.form.HtmlComponent({
			html: t("Get the code from the google authenticator app on your mobile device and fill it in below") + '.',
			cls: 'login-text-comp'
		});

		this.secretField = new Ext.form.TextField({
			itemId: 'googleauthenticator_code',
			fieldLabel: t('Code'),
			name: 'googleauthenticator_code',
			allowBlank: false,
			anchor: '100%',
			autocomplete: "one-time-code"
		});

		//nested panel is required so that submit button is inside form. 
		//Otherwise firefox won't prompt to save password and all browsers won't handle "enter" to submit.
		var panel = new Ext.Panel({
			items: [{
					xtype: 'fieldset',
					items: [
						this.secretText,
						this.secretField
					]
				}],
			bbar: [
				this.resetButton = new Ext.Button({
					text: t("Cancel"),
					handler: this.cancel,
					scope: this
				}),
				'->',
				this.nextButton = new Ext.Button({
					type: "submit",
					text: t("Next"),
					handler: this.submit,
					scope: this
				})
			]
		});

		Ext.apply(this, {
			layout: "fit",
			items: [
				panel
			]
		});

		go.googleauthenticator.GoogleauthenticatorPanel.superclass.initComponent.call(this);
	},
	setErrors: function (errors) {
		for (var key in errors) {

			switch (parseInt(errors[key].code)) {
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
