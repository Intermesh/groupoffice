Ext.define('go.modules.community.oauth2client.DefaultClientCombo', {
	extend: go.form.ComboBox,
	displayField: 'name',
	valueField: 'id',
	allowBlank: true,
	triggerAction: 'all',
	editable: false,
	selectOnFocus: true,
	forceSelection: true,
	name: 'defaultClientId',
	store: {
		xtype: "gostore",
		fields: ['id', 'name'],
		entityStore: "DefaultClient",
	}
});


Ext.reg("defaultclientcombo", go.modules.community.oauth2client.DefaultClientCombo);
