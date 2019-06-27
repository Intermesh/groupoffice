go.users.CreateUserPasswordPanel = Ext.extend(Ext.form.FormPanel, {

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
						minLength: go.Modules.get("core","core").settings.passwordMinLength,
						anchor: '100%',
						listeners: {
							afterrender: function(cmp) {
								cmp.el.set({
									autocomplete: "new-password"
								});
							},
							generated : function(field, pass) {
								this.passwordField2.setValue(pass);
							},
							scope: this
						}
						
					}),
					
					this.passwordField2 = new Ext.form.TextField({		
						minLength: go.Modules.get("core","core").settings.passwordMinLength,				
						allowBlank: false,
						anchor: '100%',
						inputType: 'password',
						fieldLabel: t("Confirm password"),
						afterrender: function(cmp) {
							cmp.el.set({
								autocomplete: "new-password"
							});
						},
						submit: false
					})]
			}
		];
		go.users.CreateUserPasswordPanel.superclass.initComponent.call(this);
	}
});



