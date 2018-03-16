go.systemsettings.NotificationsPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('Notifications'),
			autoScroll: true,
			iconCls: 'ic-notifications',
			items: [{
					xtype: "fieldset",
					title: t('Outgoing E-mail (SMTP)'),
					items: [
						{
							xtype: 'textfield',
							name: 'systemEmail',
							fieldLabel: t('System e-mail'),
						},{
							xtype: 'textfield',
							name: 'smtpHost',
							fieldLabel: t('Hostname'),
						}, {
							xtype: 'numberfield',
							name: 'smtpPort',
							fieldLabel: t('Port'),
							decimals: 0
						}, {
							xtype: 'textfield',
							name: 'smtpUsername',
							fieldLabel: t('Username')
						}, {
							xtype: 'textfield',
							name: 'smtpPassword',
							fieldLabel: t('Password')
						}, {
							xtype: 'combo',
							name: 'smtpEncryption',
							fieldLabel: t('Encryption'),
							mode: 'local',
							editable: false,
							triggerAction: 'all',
							store: new Ext.data.ArrayStore({
								fields: [
									'value',
									'display'
								],
								data: [['tls', 'TLS'], ['ssl', 'SSL'], [null, 'None']]
							}),
							valueField: 'value',
							displayField: 'display'
						}
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);
	},

	submit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function(options, success, response) {
				cb.call(scope, success);
			},
			scop: scope
		});
	},

	load: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/get",
			callback: function(options, success, response) {
				this.getForm().setValues(response);
				
				cb.call(scope, success);
			},
			scope: this
		});
	}


});

//GO.mainLayout.onReady(function(){
//	go.systemSettingsDialog.addPanel('email', go.systemsettings.EmailPanel);
//});

