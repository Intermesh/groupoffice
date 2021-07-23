go.systemsettings.NotificationsPanel = Ext.extend(go.systemsettings.Panel, {
	initComponent: function () {
		
		Ext.apply(this, {
			itemId: "notifications", //makes it routable
			title: t('Notifications'),
			autoScroll: true,
			iconCls: 'ic-notifications',
			items: [{
					defaults: {
						width: dp(360)
					},
					xtype: "fieldset",
					title: t('Outgoing E-mail (SMTP)'),
					bbar: [
						{
							text: t("Send test message"),
							handler: this.sendTestMessage,
							scope: this
						}
					],
					items: [
						{
							xtype: 'textfield',
							name: 'systemEmail',
							fieldLabel: t('System e-mail'),
						}, {
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
							fieldLabel: t('Password'),
							inputType: "password",
							autocomplete: "new-password"
						}, {
							xtype: 'combo',
							name: 'smtpEncryption',
							fieldLabel: t('Encryption'),
							mode: 'local',
							editable: false,
							triggerAction: 'all',
							value: "tls",
							store: new Ext.data.ArrayStore({
								fields: [
									'value',
									'display'
								],
								data: [['tls', 'TLS'], ['ssl', 'SSL'], [null, 'None']]
							}),
							valueField: 'value',
							displayField: 'display',
							listeners: {
								change: function (combo, newVal, oldVal) {
									this.getForm().findField('smtpEncryptionVerifyCertificate').setDisabled(newVal == null);
								},
								scope: this
							}
						}, {
							xtype: 'xcheckbox',
							name: "smtpEncryptionVerifyCertificate",
							checked: true,
							hideLabel: true,
							disabled: false,
							boxLabel: t("Verify SSL certificate")							
						}
					]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);		
		
	},
	
	afterRender: function() {
		
		go.systemsettings.NotificationsPanel.superclass.afterRender.call(this);
		
		var f = this.getForm();
		
		f.findField('smtpEncryptionVerifyCertificate').setDisabled(!f.findField('smtpEncryption').getValue());
	},

	sendTestMessage : function() {
		this.getEl().mask(t("Sending..."));
		var me = this;
		go.Jmap.request({
			method: "core/Settings/sendTestMessage",
			params: this.getForm().getFieldValues(true)
		}).then(function(response) {
			Ext.MessageBox.alert(
				t("Success"),
				t("A message was sent successfully to {email}").replace('{email}', me.getForm().findField('systemEmail').getValue())
			);
		}).catch(function(response) {
			var error = "";
			error = "<br /><br />" + response.message;

			Ext.MessageBox.alert(
				t("Failed"),
				t("Failed to send message to {email}").replace('{email}', me.getForm().findField('systemEmail').getValue() + error)
			);
		}).finally(function() {
			me.getEl().unmask();
		});


	}

});

