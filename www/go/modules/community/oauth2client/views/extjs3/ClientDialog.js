go.modules.community.oauth2client.ClientDialog = Ext.extend(go.form.Dialog, {
	title: t('OAuth2 Connection'),
	entityStore: "Oauth2Client",
	autoHeight: true,
	width: dp(800),
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
				},this.defaultClientCombo = new go.modules.community.oauth2client.DefaultClientCombo({
					fieldLabel: t("Provider", "oauth2client", "community"),
					anchor: '100%'
				}),
				{
					xtype: 'textfield',
					name: 'clientId',
					fieldLabel: t("Client Id"),
					anchor: '100%',
					allowBlank: false
				},
				{
					xtype: 'textfield',
					name: 'clientSecret',
					fieldLabel: t("Client Secret"),
					anchor: '100%',
					allowBlank: false
				},
				{
					xtype: 'textfield',
					name: 'projectId',
					fieldLabel: t("API Project Id"),
					anchor: '100%',
					allowBlank: false
				}
			]
		}
		];
	}
});
