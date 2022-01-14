Ext.define('go.modules.community.oauth2client.DefaultClientCombo', {
	extend: go.form.ComboBoxReset,
	fieldLabel: t("Authentication Method", 'email', 'community'),
	displayField: 'authenticationMethod',
	valueField: 'id',
	allowBlank: true,
	triggerAction: 'all',
	editable: false,
	selectOnFocus: true,
	forceSelection: true,
	emptyText: t("User name and password", 'oauth2client', 'community'),
	store: {
		xtype: "gostore",
		fields: ['id', 'authenticationMethod','imapHost','imapPort','imapEncryption','smtpHost','smtpPort','smtpEncryption'],
		entityStore: "DefaultClient",
	}
});


Ext.reg("defaultclientcombo", go.modules.community.oauth2client.DefaultClientCombo);
