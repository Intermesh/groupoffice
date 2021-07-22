go.users.CreateUserAccountPanel = Ext.extend(Ext.form.FormPanel, {

	isValid: function () {

		return  this.getForm().isValid();
	},
	
	initComponent: function () {
		this.items = [{
				xtype: 'fieldset',
				items: [
					//Add a hidden submit button so the form will submit on enter
					{
						xtype: "button",
						hidden: true,
						hideMode: "offsets",
						type: "submit",
						handler: function() {
							this.ownerCt.continue();
						},
						scope: this
					},
					{
						xtype: 'textfield',
						name: 'username',
						fieldLabel: t("Username"),
						anchor: '100%',
						allowBlank: false,
						autocomplete: "off",
						regex: /^[A-Za-z0-9_\-\.\@]*$/,
						regexText: t("You have invalid characters in the username") + " (a-z, 0-9, -, _, ., @)."
					}, {
						xtype: 'textfield',
						name: 'displayName',
						fieldLabel: t("Display name"),
						anchor: '100%',
						allowBlank: false
					},
					this.emailField = new Ext.form.TextField({
						fieldLabel: t('E-mail'),
						name: 'email',
						vtype:'emailAddress',
						needPasswordForChange: true,
						allowBlank:false,
						anchor: '100%',
						listeners: {
							change: function(field, v, old) {
								if(!this.recoveryEmailField.getValue()) {
									this.recoveryEmailField.setValue(v);
								}
							},
							scope: this
						}
					}),
					this.recoveryEmailField = new Ext.form.TextField({
						fieldLabel: t("Recovery e-mail"),
						name: 'recoveryEmail',
						needPasswordForChange: true,
						vtype:'emailAddress',
						allowBlank:false,
						anchor: '100%',
						hint:t('The recovery e-mail is used to send a forgotten password request to.<br />Please use an email address that you can access from outside Group-Office.')
					})
				]
			}
		];
		 go.users.CreateUserAccountPanel.superclass.initComponent.call(this);
	},
	
	onSubmitStart : function(value) {}, // override by serverclient 
	onSubmitComplete : function(user, result) {}
});


