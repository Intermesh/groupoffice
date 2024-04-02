/* global Ext, GO, go */

go.login.ForcePasswordChangePanel = Ext.extend(go.login.BaseLoginPanel, {

	layout: "fit",
	initComponent: function () {

		this.passwordField = new Ext.form.TextField({
			itemId: 'password',
			fieldLabel: t("Password"),
			name: 'password',
			inputType: 'password',
			allowBlank: false,
			anchor: '100%',
			autocomplete: 'current-password',
			validateOnBlur: false
		});

		var items = [
			new GO.form.HtmlComponent({
				html: t("A password change is required. Please enter a new password."),
				cls: 'login-text-comp'
			}),
			this.currentPasswordField = new Ext.form.Hidden({
				value: go.AuthenticationManager.password,
				name: "currentPassword"
			}),
			this.passwordField,
			this.confirmPasswordField = new Ext.form.TextField({
				xtype:'textfield',
				inputType: 'password',
				name:'confirmPassword',
				anchor: '100%',
				fieldLabel: t('Confirm password'),
				allowBlank: false
			})
		];


		//nested panel is required so that submit button is inside form. 
		//Otherwise firefox won't prompt to save password and all browsers won't handle "enter" to submit
		var panel = new Ext.Panel({
			layout: "fit",

			bbar: [
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

		this.on("show", () => {
			this.currentPasswordField.setValue(go.AuthenticationManager.password);
		})
	},

	setErrors: function (errors) {

		for (var key in errors) {

				switch (parseInt(errors[key].code)) {
					case 1:
						this.passwordField.markInvalid(t('Password is required'));
						break;
					case 5:
						this.passwordField.markInvalid(t('Not found'));
						break;
					case 10:
					default:
						this.passwordField.markInvalid(errors[key].description ?? t('Invalid password'));
						break;
				}

		}
	},



	submit: function () {

		if(this.passwordField.getValue() != this.confirmPasswordField.getValue()) {
			this.confirmPasswordField.markInvalid(t("The passwords didn't match"));
			return;
		}
		return this.supr().submit.call(this);
	},
	focus : function() {
		this.passwordField.focus();
	}
});

go.AuthenticationManager.register('forcepasswordchange', new go.login.ForcePasswordChangePanel());

