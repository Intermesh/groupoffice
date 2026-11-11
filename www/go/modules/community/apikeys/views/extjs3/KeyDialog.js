go.modules.community.apikeys.KeyDialog = Ext.extend(go.form.Dialog, {
	title: t('API Key'),
	entityStore: "Key",
	autoHeight: true,
	onSubmit: function(success, serverId) {
		// The access token is only returned once by the server, when the key is created
		if(!success) {
			return;
		}
		const key = go.Db.store("Key").data[serverId];
		if(key && key.accessToken) {
			Ext.MessageBox.alert(t("Access token"), t("Copy the access token now. It's not possible to view it again.") + "<br><br><code>" + Ext.util.Format.htmlEncode(key.accessToken) + "</code>");
		}
	},
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
					},{
						xtype: 'usercombo',
						name: 'userId',
						fieldLabel: t("User"),
						allowBlank: false
					}]
			}
		];
	}
});

