go.systemsettings.NotificationsPanel = Ext.extend(Ext.form.FormPanel, {
	initComponent: function () {
		Ext.apply(this, {
			title: t('Notifications'),
			autoScroll: true,
			iconCls: 'ic-notifications',
			items: [{
					defaults: {
						width: dp(240)
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
									this.getForm().findField('debugEmail').setDisabled(!checked);
								},
								scope: this
							}
						}, {
							xtype: 'textfield',
							name: 'debugEmail',
							disabled: true,
							fieldLabel: t("E-mail")
						}]
				}]
		});

		go.systemsettings.NotificationsPanel.superclass.initComponent.call(this);
	},
	
	sendTestMessage : function() {
		go.Jmap.request({
			method: "core/core/Settings/sendTestMessage",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				if(success) {
					Ext.MessageBox.alert(
						t("Success"), 
						t("A message was sent successfully to {email}").replace('{email}', this.getForm().findField('systemEmail').getValue())
					);
				} else
				{
					var error = "";
					if(response[0] == "error") {
						error = "<br /><br />" + response[1].message;
					}
					Ext.MessageBox.alert(
						t("Failed"), 
						t("Failed to send message to {email}").replace('{email}', this.getForm().findField('systemEmail').getValue() + error) 
					);
				}
			},
			scope: this
		});
	},

	submit: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/set",
			params: this.getForm().getFieldValues(),
			callback: function (options, success, response) {
				cb.call(scope, success);
			},
			scope: scope
		});
	},

	load: function (cb, scope) {
		go.Jmap.request({
			method: "core/core/Settings/get",
			callback: function (options, success, response) {
				
				var f = this.getForm();
				f.setValues(response);				
				f.findField("enableEmailDebug").setValue(!GO.util.empty(f.findField('debugEmail').getValue()));
				

				cb.call(scope, success);
			},
			scope: this
		});
	}


});

//GO.mainLayout.onReady(function(){
//	go.systemSettingsDialog.addPanel('email', go.systemsettings.EmailPanel);
//});

