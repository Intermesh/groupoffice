/* global Ext, GO, go */

go.login.UsernamePanel = Ext.extend(go.login.BaseLoginPanel, {

	layout: "fit",
	initComponent: function () {


		this.usernameField = new Ext.form.TextField({
			itemId: 'username',
			fieldLabel: t("Username"),
			name: 'username',
			allowBlank: false,
			anchor: '100%',
			autocomplete: "username"
			
		});

		this.passwordField = new Ext.form.TextField({
			itemId: 'password',
			fieldLabel: t("Password"),
			name: 'password',
			inputType: 'password',
			allowBlank: false,
			anchor: '100%',
			autocomplete: 'current-password'
		});

		var items = [

			this.usernameField,
			this.passwordField,
			{
				hidden: GO.settings.config.logoutWhenInactive > 0,
				itemCls: 'go-login-remember',
				hideLabel: true,
				xtype: "xcheckbox",
				name: "remember",
				value: false,
				boxLabel: t("Remember my login on this computer until I press logout"),
				listeners: {
					check: function (checkbox, checked) {
						go.AuthenticationManager.rememberLogin = checked;
					}
				}
			}
		];


		//nested panel is required so that submit button is inside form. 
		//Otherwise firefox won't prompt to save password and all browsers won't handle "enter" to submit
		var panel = new Ext.Panel({
			id: 'usernameCheck',
			layout: "vbox",
			layoutConfig: {
				align: "stretch",
				pack: "center"
			},
			bbar: [
				this.forgotBtn = new Ext.Button({
					cls: "go-login-forgot-username",
					text: t("Forgot login credentials?"),
					handler: this.showForgot,
					scope: this
				}),
				'->',
				this.nextButton = new Ext.Button({
					type: "submit",
					text: t("Next"),
					handler: this.submit,
					scope: this,
					cls: "primary"
				})
			],
			items: [
				{
					xtype: "fieldset",
					items: items
				}
			]
		});

		this.items = [panel];

		go.login.UsernamePanel.superclass.initComponent.call(this);
	},

	setErrors: function (errors) {

		for (var key in errors) {
			if (key === "password") {
				switch (parseInt(errors[key].code)) {
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
				Ext.MessageBox.alert("Error",t("Bad username or password"));
			}
		}
	},

	showForgot: function () {

		if(GO.settings.config.lostPasswordURL) {
			window.location.replace(GO.settings.config.lostPasswordURL);
			return;
		}

		var forgotDlg = new go.login.ForgotDialog();
		forgotDlg.show();
	},

	submit: function () {

		if (!this.getForm().isValid() || this.submitting) {
			return;
		}
		this.submitting = true;
		this.getEl().mask();
		
		var username = this.usernameField.getValue();

		go.AuthenticationManager.getAvailableMethods(username, this.passwordField.getValue(), function (authMan, success, result) {
			this.getEl().unmask();
			this.submitting = false;
			if (success) {
				this.onSuccess();
			} else {
				this.setErrors(result.errors);
			}
		}, this);
	},
	focus : function() {
		this.usernameField.focus();
	}
});
