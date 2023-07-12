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
		sortBy: 'name',
		listeners: {
			'load': (store) => {
				// fetch the rest from emai laccount and append
				GO.request({
					url: 'email/account/store',
					success: (options, response, result) =>
					{
						for(const item of result.results) {
							store.add(new store.recordType({
								id:'old_'+item.id,
								host: item.smtp_host,
								name: item.name + ' - ' + item.email,
								fromName: item.name,
								fromEmail: item.email
							}, 'old_'+item.id));
						}
					}
				});

			}
		}
	}
});

Ext.reg("smtpaccountcombo", go.smtp.AccountCombo);
