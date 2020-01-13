go.systemsettings.NotificationsPanel = Ext.extend(go.systemsettings.Panel, {
	initComponent: function () {
		
		Ext.apply(this, {
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
							inputType: "password"
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
				}, {
					xtype: "fieldset",
					title: t('Debug'),					
					items: [{
							xtype: 'xcheckbox',
							name: "enableEmailDebug",
							submit: false,
							hideLabel: true,
							boxLabel: t("Send all system notifications to the specified e-mail address"),
							listeners: {
								check: function (checkbox, checked) {
									this.getForm().findField('debugEmail').setReadOnly(!checked);
								},
								scope: this
							}
						}, {
							xtype: 'textfield',
							name: 'debugEmail',
							readOnly: true,
							fieldLabel: t("E-mail"),
							width: dp(240)
						}]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);		
		
	},
	
	onSubmit: function (cb, scope) {
		
		//clear debug mail on save
		if(!this.getForm().findField('enableEmailDebug').checked) {
			this.getForm().findField('debugEmail').setValue('');
		}
		
		go.systemsettings.NotificationsPanel.superclass.onSubmit.call(this, cb, scope);
	},
	
	afterRender: function() {
		
		go.systemsettings.NotificationsPanel.superclass.afterRender.call(this);
		
		var f = this.getForm();
		
		f.findField('smtpEncryptionVerifyCertificate').setDisabled(!f.findField('smtpEncryption').getValue());
		f.findField("enableEmailDebug").setValue(!GO.util.empty(f.findField('debugEmail').getValue()));
	},
	
	sendTestMessage : function() {
		this.getEl().mask(t("Sending..."));
		
		go.Jmap.request({
			method: "core/Settings/sendTestMessage",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				this.getEl().unmask();
				if(success) {
					Ext.MessageBox.alert(
						t("Success"), 
						t("A message was sent successfully to {email}").replace('{email}', this.getForm().findField('systemEmail').getValue())
					);
				} else
				{
					var error = "";					
					error = "<br /><br />" + response.message;
					
					Ext.MessageBox.alert(
						t("Failed"), 
						t("Failed to send message to {email}").replace('{email}', this.getForm().findField('systemEmail').getValue() + error) 
					);
				}
			},
			scope: this
		});
		
		
	}

});

