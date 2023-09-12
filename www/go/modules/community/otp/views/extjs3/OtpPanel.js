go.modules.community.otp.OtpPanel = Ext.extend(go.login.BaseLoginPanel, {

	initComponent: function () {

		this.secretText = new GO.form.HtmlComponent({
			html: t("Get the code from the OTP Authenticator app on your mobile device and fill it in below") + '.',
			cls: 'login-text-comp'
		});

		this.secretField = new go.form.PasteButtonField({
			itemId: 'otp_code',
			fieldLabel: t('Code'),
			name: 'otp_code',
			allowBlank: false,
			anchor: '100%',
			autocomplete: "one-time-code"
		});

		//nested panel is required so that submit button is inside form. 
		//Otherwise firefox won't prompt to save password and all browsers won't handle "enter" to submit.
		const panel = new Ext.Panel({
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
					cls: "primary",
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

		go.modules.community.otp.OtpPanel.superclass.initComponent.call(this);
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

go.AuthenticationManager.register('otpauthenticator', new go.modules.community.otp.OtpPanel());
