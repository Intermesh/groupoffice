go.oauth.ClientDialog = Ext.extend(go.form.Dialog, {
	title: t('Client'),
	entityStore: "OauthClient",
	autoHeight: true,
	initFormItems: function () {
		return [{
				xtype: 'fieldset',
				items: [
					{
						xtype: 'textfield',
						name: 'name',
						fieldLabel: t("Name"),
						anchor: '100%',						
						allowBlank: false
					},
					{
						xtype: 'textfield',
						name: 'identifier',
						fieldLabel: t("Identifier"),
						anchor: '100%',
						allowBlank: false
					},
					{
						xtype: "xcheckbox",
						name: 'isConfidential',
						boxLabel: t("Is confidential"),
						listeners: {
							check: function(cb, checked) {
								this.secretField.setDisabled(!checked);
							},
							scope: this
						}
					}, {
						xtype: 'textfield',
						name: 'redirectUri',
						fieldLabel: t("Redirect URI"),
						anchor: '100%',
						allowBlank: false
					},
					this.secretField = new Ext.form.TextField({
						xtype: 'textfield',
						inputType: 'password',
						autocomplete: 'new-password',
						name: 'secret',
						fieldLabel: t("Secret"),
						anchor: '100%',
						allowBlank: false,
						disabled: true
					})]
			}
		];
	}
});

