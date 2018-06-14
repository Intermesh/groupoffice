go.modules.core.users.CreateUserPasswordPanel = Ext.extend(Ext.form.FormPanel, {

	isValid: function () {
		if (this.passwordField1.getValue() != this.passwordField2.getValue()) {
			this.passwordField1.markInvalid(t("The passwords didn't match"));
			return false;
		}

		return  this.getForm().isValid();
	},
	
	initComponent: function () {
		this.items = [{
				xtype: 'fieldset',
				title: t('Password'),
				items: [
					this.passwordField1 = new go.form.PasswordGeneratorField({						
						allowBlank: false,
						anchor: '100%',
							listeners: {
							generated : function(field, pass) {
								this.passwordField2.setValue(pass);
							},
							scope: this
						}
						
					}),
					
					this.passwordField2 = new Ext.form.TextField({						
						allowBlank: false,
						anchor: '100%',
						inputType: 'password',
						fieldLabel: t("Confirm password"),
						name: 'passwordConfirm'
					})]
			}
		];
		 go.modules.core.users.CreateUserPasswordPanel.superclass.initComponent.call(this);
	}
});



