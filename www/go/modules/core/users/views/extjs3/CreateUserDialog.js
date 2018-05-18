go.modules.core.users.CreateUserDialog = Ext.extend(go.form.Dialog, {
	title: t('Create user'),
	entityStore: go.Stores.get("User"),
	autoHeight: true,
	validate: function () {
		if (this.passwordField1.getValue() != this.passwordField2.getValue()) {
			this.passwordField1.markInvalid(t("The passwords didn't match"));
			return false;
		}

		return go.modules.core.users.CreateUserDialog.validate.call(this);
	},
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				title: t('User'),
				items: [
					{
						xtype: 'textfield',
						name: 'username',
						fieldLabel: t("Username"),
						anchor: '100%',
						allowBlank: false
					}, {
						xtype: 'textfield',
						name: 'displayName',
						fieldLabel: t("Display name"),
						anchor: '100%',
						allowBlank: false
					},
					this.emailField = new Ext.form.TextField({
						fieldLabel: t('Email'),
						name: 'email',
						vtype:'emailAddress',
						needPasswordForChange: true,
						allowBlank:false,
						anchor: '100%'
					}),
					this.recoveryEmailField = new Ext.form.TextField({
						fieldLabel: t("Recovery e-mail"),
						name: 'recoveryEmail',
						needPasswordForChange: true,
						vtype:'emailAddress',
						allowBlank:false,
						anchor: '100%',
						hint:t('The recovery e-mail is used to send a forgotten password request to.')+'<br>'+t('Please use an email address that you can access from outside Group-Office.')
					})
				]
			},{
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
			},{
				xtype: 'fieldset',
				title: t("Groups"),
				items: [
					new go.form.multiselect.Field({
						hint: t("Add the groups this user must be a member of"),
						name: "groups",
						idField: "groupId",
						displayField: "name",
						entityStore: go.Stores.get("Group"),
						
						fieldLabel: t("Groups"),
						storeBaseParams:{
							filter: {"includeUsers" : false}
						}
					})
				]
			}
		];
	}
});

