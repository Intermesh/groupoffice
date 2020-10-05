/* global Ext, go, GO */

go.smtp.AccountCombo = Ext.extend(go.form.ComboBox, {
	fieldLabel: t("Account (SMTP)"),
	hiddenName: 'smtpAccountId',
	anchor: '100%',
	emptyText: t("Please select..."),
	pageSize: 50,
	valueField: 'id',
	displayField: 'name',
	triggerAction: 'all',
	editable: true,
	selectOnFocus: true,
	forceSelection: true,
	store: {
		xtype: "gostore",
		fields: ['id', 'fromName', 'fromEmail', 'host', {name: 'name', convert: function(v, rec) {return rec.fromName + ": " + rec.fromEmail;}}],
		entityStore: "SmtpAccount",
		sortBy: 'name'
	}
});

Ext.reg("smtpaccountcombo", go.smtp.AccountCombo);
