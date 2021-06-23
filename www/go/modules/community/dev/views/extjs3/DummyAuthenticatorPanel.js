go.modules.community.dev.DummyAuthenticatorPanel = Ext.extend(go.login.BaseLoginPanel, {

	initComponent: function () {

		this.secretText = new GO.form.HtmlComponent({
			html: t("Enter 'dummy' to pass.") + '.',
			cls: 'login-text-comp'
		});

		this.secretField = new Ext.form.TextField({
			fieldLabel: t('Code'),
			name: 'code',
			allowBlank: false,
			anchor: '100%'
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

		go.modules.community.dev.DummyAuthenticatorPanel.superclass.initComponent.call(this);
	}
});

go.AuthenticationManager.register('dummy', new go.modules.community.dev.DummyAuthenticatorPanel());
