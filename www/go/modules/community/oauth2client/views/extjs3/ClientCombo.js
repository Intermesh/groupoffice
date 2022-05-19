Ext.define('go.modules.community.oauth2client.ClientCombo', {
	extend: go.form.ComboBoxReset,
	displayField: 'name',
	valueField: 'id',
	allowBlank: true,
	triggerAction: 'all',
	editable: false,
	selectOnFocus: true,
	forceSelection: true,
	name: 'oauth2_client',
	store: {
		xtype: "gostore",
		fields: ['id', 'name', 'defaultClientId'], // TODO: SMTP and IMAP settings from defaultClient
		entityStore: "Oauth2Client",
	}
});


Ext.reg("oauth2clientcombo", go.modules.community.oauth2client.ClientCombo);
