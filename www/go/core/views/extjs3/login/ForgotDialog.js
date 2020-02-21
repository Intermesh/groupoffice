go.login.ForgotDialog = Ext.extend(go.Window, {
	width: dp(400),
	autoHeight: true,
	modal: true,

	initComponent: function () {

		this.emailField = new Ext.form.TextField({
			fieldLabel: t("E-mail"),
			name: 'email',
			vtype: 'emailAddress',
			allowBlank: false,
			anchor: '100%'
		});

		Ext.apply(this, {
			title: t('Request a recovery email'),
			items: [{
					xtype: 'form',
					items: [{
							xtype: 'panel',
							items: {
								xtype: "fieldset",
								items: [
									{html: t("Enter your email address")},
									this.emailField
								]
							},
							bbar: [
								'->',
								{
									xtype: "button",
									text: t("Send"),
									type: "submit",
									handler: this.submit,
									scope: this
								}
							]
						}]
				}]
		});

		go.login.ForgotDialog.superclass.initComponent.call(this);
	},

	focus: function () {
		this.emailField.focus();
	},

	submit: function () {

		this.getEl().mask();

		Ext.Ajax.request({
			url: go.AuthenticationManager.getAuthUrl(),
			jsonData: {forgot: true, email: this.emailField.getValue()},
			scope: this,
			callback: function (options, success, response) {
				this.getEl().unmask();
				if (!success) {
					Ext.MessageBox.alert(t("Error"), t("Sorry, an error occurred") + ": " +  response.statusText);
				} else {
					go.Notifier.msg({
						time: 3000,
						iconCls: 'ic-email',
						title: t("Recovery e-mail sent"),
						description: t('An e-mail with instructions has been sent to your e-mail address.')
					});
				}
				this.close();
			}
		});

	}
});
