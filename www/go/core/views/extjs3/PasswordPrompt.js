go.PasswordPrompt = Ext.extend(go.Window, {

	initComponent: function () {
		
		Ext.applyIf(this, {
			text: t('Provide your password.'),
			title: t('Password required'),
			modal:true,
			width: dp(400)
		});
		
		this.formPanel = new Ext.FormPanel({
			layout: 'form',
			items: [
				this.passwordFieldset = new Ext.form.FieldSet({
//					labelWidth: dp(120),
					items: [
						//Add a hidden submit button so the form will submit on enter
						new Ext.Button({
							hidden: true,
							hideMode: "offsets",
							type: "submit",
							handler: function() {
								this.okPressed();
							},
							scope: this
						}),
						this.passwordText = new Ext.Container({
							html: this.text,
							style: {
								marginBottom: dp(8) + 'px'
							}
						}),
						this.passwordField = new Ext.form.TextField({
							fieldLabel: t("Password"),
							hideLabel:true,
							name: 'password',
							inputType: 'password',
							autocomplete: "current-password",
							allowBlank: false,
							anchor: '100%'
						})
					]
				})
			]
		});

		Ext.apply(this, {
			autoHeight:true,
			closeAction: 'cancelPressed',
			items: [
				this.formPanel
			],
			buttons: [{
					text: t("Continue"),
					handler: this.okPressed,
					scope: this
				}]
		});
		
		this.addEvents({
			'cancel':true,
			'ok':true
		});

		go.PasswordPrompt.superclass.initComponent.call(this);
	},
	focus: function() {
		this.passwordField.focus();
	},
	
	okPressed: function () {
		
		if(this.formPanel.getForm().isValid()){	
			this.fireEvent('ok', this.passwordField.getValue());
			this.close();
		}
	},
	cancelPressed: function () {
		this.fireEvent('cancel');
		this.close();
	}
});
